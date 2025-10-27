<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
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

    public function createFromArray(array $groupCredentialArray): ?GroupCredential
    {
        $group = $this->em->getRepository(Group::class)->findOneBy([
            'name' => $groupCredentialArray['group']
        ]);
        $credential = $this->em->getRepository(Credential::class)->findOneBy([
            'role' => $groupCredentialArray['credential'],
        ]);
        if (!$group || !$credential) {
            return null;
        }

        $groupCredential = new GroupCredential();
        $groupCredential
            ->setGroupe($group)
            ->setCredential($credential)
            ->setAllowed($groupCredentialArray['allowed'])
            ->setStatusAllowed($groupCredentialArray['statusAllowed']);

        $this->em->persist($groupCredential);

        return $groupCredential;
    }
}
