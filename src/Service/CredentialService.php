<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

class CredentialService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function toggleAll(array $groupCredentials, array $credentials, Group $group, bool $checked): void
    {
        $existingCredentials = [];
        foreach ($groupCredentials as $groupCredential) {
            $existingCredentials[$groupCredential->getCredential()->getId()] = $groupCredential;
        }

        /** @var Credential $credential */
        foreach ($credentials as $credential) {
            if (!array_key_exists((int)$credential->getId(), $existingCredentials)) {
                $groupCredential = new GroupCredential();
                $groupCredential->setGroupe($group);
                $groupCredential->setCredential($credential);
                $groupCredential->setAllowed($checked);

                $this->em->persist($groupCredential);
            }
        }
    }

    public function allowedByStatus(?string $group, ?string $statusCred, ?string $cred): void
    {
        /** @var Group $groupObj */
        $groupObj = $this->em->getRepository(Group::class)->findOneBy(['name' => $group]);
        $credential = $this->em->getRepository(Credential::class)->findOneBy(['role' => $statusCred]);

        $cred = $this->em->getRepository(Credential::class)->findOneBy(['role' => $cred]);

        if (!$credential) {
            $credential = new Credential();
            $credential->setRole((string)$statusCred);
            $credential->setLibelle((string)$statusCred);
            $credential->setRubrique($cred?->getRubrique());
            $credential->setTri(0);
            $credential->setVisible(true);

            $this->em->persist($credential);
        }

        $groupCred = new GroupCredential();
        $groupCred->setGroupe($groupObj);
        $groupCred->setCredential($credential);
        $groupCred->setAllowed(true);
    }
}
