<?php

namespace Lle\CredentialBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class CredentialVoter extends Voter
{

    private $decisionManager;
    private $em;

    public function __construct(AccessDecisionManagerInterface $decisionManager, EntityManagerInterface $em)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
    }

    protected function supports($attribute, $subject)
    {
        // vote on everything
        $credential = $this->em->getRepository(Credential::class)->findOneByRole($attribute);
        if (!$credential) {  // insert on check
            $credential = new Credential();
            $credential->setRole($attribute);
            $this->em->persist($credential);
            $this->em->flush();
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

        foreach($user->getRoles() as $group) {
            $group = str_replace('ROLE_','', $group);
            $groupObj = $this->em->getRepository(Group::class)->findOneByName($group);
            if (!$groupObj) {  // insert on check
                $groupObj = new Group();
                $groupObj->setName($group);
                $this->em->persist($groupObj);
                $this->em->flush();
            }
        }

        $credential = $this->em->getRepository(Credential::class)->findOneByRole($attribute);
        if (!$credential) {  // insert on check
            $credential = new Credential();
            $credential->setRole($attribute);
            $this->em->persist($credential);
            $this->em->flush();
        }

        $group_cred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($group, $attribute);
        if (!$group_cred) {
            $group_cred = new GroupCredential();
            $group_cred->setGroupe($groupObj);
            $group_cred->setCredential($credential);
            $group_cred->setAllowed(false);
            $this->em->persist($group_cred);
            $this->em->flush();            
        }

        // ROLE_SUPER_ADMIN can do anything! The power!
        if ($this->decisionManager->decide($token, array('ROLE_SUPER_ADMIN'))) {
            return true;
        }
        $group_cred = $this->em->getRepository(GroupCredential::class)->findGroupCred($group, $cred);
        if ($group_cred) {
            return $group_cred->isAllowed();
        } else {
            return false;
        }
    }


}
