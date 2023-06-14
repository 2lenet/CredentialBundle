<?php

namespace Lle\CredentialBundle\Security;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\GroupCredential;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class CredentialVoter extends Voter
{
    private $decisionManager;
    private $em;
    private $groupRights = [];
    private $roles = [];
    private $cache;

    public function __construct(
        AccessDecisionManagerInterface $decisionManager,
        EntityManagerInterface $em,
        CacheItemPoolInterface $cache
    ) {
        $this->decisionManager = $decisionManager;
        $this->em = $em;

        $cachedGroupRights = $cache->getItem('group_credentials');

        if (!$cachedGroupRights->isHit()) {
            $group_creds = $this->em->getRepository(GroupCredential::class)->findAll();

            foreach ($group_creds as $group_cred) {
                if ($group_cred->isAllowed()) {
                    $group_name = $group_cred->getGroupe()->getName();
                    $cred_name = $group_cred->getCredential()->getRole();
                    $credListeStatus = $group_cred->getCredential()->getListeStatus();

                    if (!array_key_exists($group_name, $this->groupRights)) {
                        $this->groupRights[$group_name] = [];
                    }

                    $this->groupRights[$group_name][$cred_name] = [
                        "listeStatus" => $credListeStatus,
                        "statusAllowed" => $group_cred->isStatusAllowed(),
                    ];
                }
            }

            $cachedGroupRights->set($this->groupRights);
            $cache->save($cachedGroupRights);
        } else {
            $this->groupRights = $cachedGroupRights->get();
        }

        $cachedRoles = $cache->getItem('all_credentials');

        if (!$cachedRoles->isHit()) {
            $all_creds = $this->em->getRepository(Credential::class)->findAll();

            foreach ($all_creds as $cred) {
                $this->roles[] = $cred->getRole();
            }

            $cachedRoles->set($this->roles);
            $cache->save($cachedRoles);
        } else {
            $this->roles = $cachedRoles->get();
        }

        $this->cache = $cache;
    }

    protected function supports($attribute, $subject): bool
    {
        // vote on everything
        if (!in_array(
            $attribute,
            [
                'IS_AUTHENTICATED_REMEMBERED',
                'ROLE_USER',
                'IS_AUTHENTICATED_ANONYMOUS',
                'IS_AUTHENTICATED_FULLY',
                'ROLE_SUPER_ADMIN',
            ]
        )) {
            if (!in_array($attribute, $this->roles)) {  // insert on check
                $credential = new Credential();
                $credential->setRole($attribute);
                $credential->setLibelle($attribute);
                $credential->setRubrique('Other');
                $credential->setTri(0);

                $arr = explode('_', $attribute);

                if (count($arr) == 3) {
                    $rubrique = $arr[1];
                    $credential->setRubrique($rubrique);
                }

                $this->em->persist($credential);
                $this->em->flush();

                $this->cache->deleteItem('all_credentials');
                $this->cache->deleteItem('group_credentials');

                $this->roles[] = $attribute;
            }
        } else {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $user->getRoles();

        // ROLE_SUPER_ADMIN can do anything! The power!
        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            return true;
        }

        foreach ($roles as $role) {
            $groupName = str_replace("ROLE_", "", $role);

            if ($this->getVote($attribute, $subject, $groupName)) {
                return true;
            }
        }

        return false;
    }

    public function getVote($attribute, $subject, $groupName): bool
    {
        if (isset($this->groupRights[$groupName]) && array_key_exists($attribute, $this->groupRights[$groupName])) {
            $credential = $this->groupRights[$groupName][$attribute];

            if ($subject && $credential["listeStatus"] && $credential["statusAllowed"]) {
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $statusProperty = $subject->getStatusProperty();

                $attribute .= "_" . strtoupper($propertyAccessor->getValue($subject, $statusProperty));

                return array_key_exists($attribute, $this->groupRights[$groupName]);
            }
        }

        return isset($this->groupRights[$groupName]) && array_key_exists($attribute, $this->groupRights[$groupName]);
    }
}
