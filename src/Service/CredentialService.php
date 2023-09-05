<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

class CredentialService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function toggleAll(array $groupCredentials, array $credentials, Group $group, bool $checked): void
    {
        $existingCredentials = [];
        foreach ($groupCredentials as $groupCredential) {
            $existingCredentials[$groupCredential->getCredential()->getId()] = $groupCredential;
        }

        /** @var Credential $credential */
        foreach ($credentials as $credential) {
            if (!array_key_exists($credential->getId(), $existingCredentials)) {
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
        $groupObj = $this->em->getRepository(Group::class)->findOneByName($group);
        $credential = $this->em->getRepository(Credential::class)->findOneByRole($statusCred);

        $cred = $this->em->getRepository(Credential::class)->findOneByRole($cred);

        if (!$credential) {
            $credential = new Credential();
            $credential->setRole($statusCred);
            $credential->setLibelle($statusCred);
            $credential->setRubrique($cred->getRubrique());
            $credential->setTri(false);
            $credential->setVisible(true);

            $this->em->persist($credential);
        }

        $groupCred = new GroupCredential();
        $groupCred->setGroupe($groupObj);
        $groupCred->setCredential($credential);
        $groupCred->setAllowed(true);
    }
}
