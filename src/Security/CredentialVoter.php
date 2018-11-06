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
        if (!in_array($attribute, ['IS_AUTHENTICATED_REMEMBERED','ROLE_USER'])) {
            $credential = $this->em->getRepository(Credential::class)->findOneByRole($attribute);
            if (!$credential) {  // insert on check
                $credential = new Credential();
                $credential->setRole($attribute);
                $credential->setRubrique('Other');
                $this->em->persist($credential);
                $this->em->flush();
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
            $group_cred = $this->em->getRepository(GroupCredential::class)->findOneGroupCred($role, $attribute);
            if ($group_cred) {
                return $group_cred->isAllowed();
            } 
	}
        return false;
    }


}
