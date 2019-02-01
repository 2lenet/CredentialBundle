<?php

namespace Lle\CredentialBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class CredentialVoter extends Voter
{

    private $decisionManager;
    private $em;
    private $groupRights = [];
    private $roles = [];
    private $cache;


    public function __construct(AccessDecisionManagerInterface $decisionManager, EntityManagerInterface $em, AdapterInterface $cache)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
        $cachedGroupRights = $cache->getItem('group_credentials');
        if (!$cachedGroupRights->isHit()) {
            $group_creds = $this->em->getRepository(GroupCredential::class)->findAll();
            foreach($group_creds as $group_cred) {
                if ($group_cred->isAllowed()) {
                    $group_name = $group_cred->getGroupe()->getName();
                    $cred_name = $group_cred->getCredential()->getRole();
                    if (!array_key_exists($group_name, $this->groupRights)) {
                        $this->groupRights[$group_name] = [];
                    }
                    $this->groupRights[$group_name][] = $cred_name;
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

    protected function supports($attribute, $subject)
    {
        // vote on everything
        if (!in_array($attribute, ['IS_AUTHENTICATED_REMEMBERED','ROLE_USER','IS_AUTHENTICATED_ANONYMOUS'])) {
            if (!in_array($attribute, $this->roles)) {  // insert on check
                $credential = new Credential();
                $credential->setRole($attribute);
                $credential->setLibelle($attribute);
                $credential->setRubrique('Other');
                $credential->setTri(0);

                $this->em->persist($credential);
                $this->em->flush();
                $this->cache->deleteItem('all_credentials');
                $this->roles[] = $attribute;
            }
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
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

        foreach($roles as $role) {
            $k = str_replace('ROLE_', '', $role);
            if (isset($this->groupRights[$k]) && in_array($attribute, $this->groupRights[$k])) {
                return true;
            }
        }
        return false;
    }


}
