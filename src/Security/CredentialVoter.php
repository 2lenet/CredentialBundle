<?php

namespace Lle\CredentialBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\GroupCredential;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CredentialVoter extends Voter
{
    public function __construct(
        private EntityManagerInterface $em,
        private CacheItemPoolInterface $cache,
        private array $groupRights = [],
        private array $roles = [],
    ) {
        $cachedGroupRights = $cache->getItem('group_credentials');

        if (!$cachedGroupRights->isHit()) {
            $groupCreds = $this->em->getRepository(GroupCredential::class)->findAll();

            foreach ($groupCreds as $groupCred) {
                if ($groupCred->isAllowed()) {
                    $groupName = $groupCred->getGroupe()->getName();
                    $credName = $groupCred->getCredential()->getRole();
                    $credListeStatus = $groupCred->getCredential()->getListeStatus();

                    if (!array_key_exists($groupName, $this->groupRights)) {
                        $this->groupRights[$groupName] = [];
                    }

                    $this->groupRights[$groupName][$credName] = [
                        "listeStatus" => $credListeStatus,
                        "statusAllowed" => $groupCred->isStatusAllowed(),
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
            $allCreds = $this->em->getRepository(Credential::class)->findAll();

            foreach ($allCreds as $cred) {
                $this->roles[] = $cred->getRole();
            }

            $cachedRoles->set($this->roles);
            $cache->save($cachedRoles);
        } else {
            $this->roles = $cachedRoles->get();
        }
    }

    protected function supports(?string $attribute, mixed $subject): bool
    {
        // vote on everything
        if (
            !in_array(
                $attribute,
                [
                    'IS_AUTHENTICATED_REMEMBERED',
                    'ROLE_USER',
                    'IS_AUTHENTICATED_ANONYMOUS',
                    'IS_AUTHENTICATED_FULLY',
                    'ROLE_SUPER_ADMIN',
                ]
            )
        ) {
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

    protected function voteOnAttribute(?string $attribute, mixed $subject, TokenInterface $token): bool
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

    public function getVote(?string $attribute, mixed $subject, ?string $groupName): bool
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
