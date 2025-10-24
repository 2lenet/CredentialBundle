<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Dto\GroupCredentialDto;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

class GroupCredentialFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function create(Group $group, Credential $credential): GroupCredential
    {
        $groupCredential = new GroupCredential();
        $groupCredential
            ->setGroupe($group)
            ->setCredential($credential);

        $this->em->persist($groupCredential);

        return $groupCredential;
    }

    public function createFromDto(GroupCredentialDto $groupCredentialDto): ?GroupCredential
    {
        $group = $this->em->getRepository(Group::class)->findOneBy([
            'name' => $groupCredentialDto->groupName
        ]);
        $credential = $this->em->getRepository(Credential::class)->findOneBy([
            'role' => $groupCredentialDto->credentialRole,
        ]);
        if (!$group || !$credential) {
            return null;
        }

        $groupCredential = new GroupCredential();
        $groupCredential
            ->setGroupe($group)
            ->setCredential($credential)
            ->setAllowed($groupCredentialDto->allowed)
            ->setStatusAllowed($groupCredentialDto->statusAllowed);

        $this->em->persist($groupCredential);

        $group->addGroupCredential($groupCredential);
        $credential->addGroupCredential($groupCredential);

        return $groupCredential;
    }
}
