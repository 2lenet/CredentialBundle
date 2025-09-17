<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Dto\GroupCredentialDto;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

class GroupCredentialFactory
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function createGroupCredential(
        Group $group,
        Credential $credential,
        bool $allowed = true,
        bool $statusAllowed = false
    ): GroupCredential {
        $groupCred = new GroupCredential();
        $groupCred->setGroupe($group);
        $groupCred->setCredential($credential);
        $groupCred->setAllowed($allowed);
        $groupCred->setStatusAllowed($statusAllowed);

        $this->em->persist($groupCred);
        $this->em->flush();

        return $groupCred;
    }

    public function createGroupCredentialDto(GroupCredential $groupCredential): ?GroupCredentialDto
    {
    /** @var GroupCredential $groupCredential */
        $groupCredentialDto = new GroupCredentialDto();
        $groupCredentialDto->groupName = $groupCredential->getGroupe()?->getName();
        $groupCredentialDto->credentialRole = $groupCredential->getCredential()->getRole();
        $groupCredentialDto->statusAllowed = $groupCredential->isStatusAllowed();
        $groupCredentialDto->allowed = $groupCredential->isAllowed();

        return $groupCredentialDto;
    }
}
