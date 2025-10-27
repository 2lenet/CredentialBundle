<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Factory\GroupCredentialFactory;
use Doctrine\ORM\EntityManagerInterface;

class CredentialService
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected GroupCredentialFactory $groupCredentialFactory,
        protected CredentialFactory $credentialFactory,
        protected ClientService $client,
    ) {
    }

    public function toggleGroup(Group $group, bool $check): void
    {
        $credentials = $this->em->getRepository(Credential::class)->findAll();
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findBy(['groupe' => $group]);

        $this->createMissingGroupCredentials($groupCredentials, $credentials, $group);
        $this->checkGroupCredentials($groupCredentials, $check);

        $this->client->toggleGroup($group, $check);
    }

    public function toggleSection(string $section, Group $group, bool $check): void
    {
        $credentials = $this->em->getRepository(Credential::class)->findBy([
            'section' => $section
        ]);
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findBy([
            'groupe' => $group,
            'credential' => $credentials
        ]);

        $this->createMissingGroupCredentials($groupCredentials, $credentials, $group);
        $this->checkGroupCredentials($groupCredentials, $check);

        $this->client->toggleSection($section, $group, $check);
    }

    public function toggleCredential(Credential $credential, Group $group, bool $check): void
    {
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findBy([
            'groupe' => $group,
            'credential' => $credential
        ]);

        $this->createMissingGroupCredentials($groupCredentials, [$credential], $group);
        $this->checkGroupCredentials($groupCredentials, $check);

        $this->client->toggleCredential($credential, $group, $check);
    }

    public function allowStatus(Credential $credential, Group $group, bool $check): void
    {
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findBy([
            'groupe' => $group,
            'credential' => $credential
        ]);

        $this->createMissingGroupCredentials($groupCredentials, [$credential], $group);
        $this->allowStatusGroupCredentials($groupCredentials, $check);

        $this->client->allowStatus($credential, $group, $check);
    }

    public function allowForStatus(Credential $credential, Group $group, string $status, bool $check): void
    {
        $credentialForStatus = $this->em->getRepository(Credential::class)->findOneBy([
            'role' => $credential->getRole() . '_' . strtoupper($status)
        ]);
        if (!$credentialForStatus) {
            $credentialForStatus = $this->credentialFactory->create(
                $credential->getRole() . '_' . strtoupper($status),
                $credential->getSection(),
                $credential->getLabel(),
            );

            $this->em->persist($credentialForStatus);
            $this->em->flush();
        }

        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findBy([
            'groupe' => $group,
            'credential' => $credentialForStatus
        ]);

        $this->createMissingGroupCredentials($groupCredentials, [$credentialForStatus], $group);
        $this->checkGroupCredentials($groupCredentials, $check);

        $this->client->allowForStatus($credential, $group, $status, $check);
    }

    /**
     * @param GroupCredential[] $groupCredentials
     * @param Credential[] $credentials
     */
    public function createMissingGroupCredentials(array &$groupCredentials, array $credentials, Group $group): void
    {
        $existingsGroupCredentials = [];
        foreach ($groupCredentials as $groupCredential) {
            $existingsGroupCredentials[$groupCredential->getCredential()?->getRole()] = $groupCredential;
        }

        foreach ($credentials as $credential) {
            if (!array_key_exists((string)$credential->getRole(), $existingsGroupCredentials)) {
                $groupCredential = $this->groupCredentialFactory->create($group, $credential);

                $this->em->persist($groupCredential);

                $groupCredentials[$credential->getRole()] = $groupCredential;
            }
        }

        $this->em->flush();
    }

    /**
     * @param GroupCredential[] $groupCredentials
     */
    public function checkGroupCredentials(array $groupCredentials, bool $check): void
    {
        foreach ($groupCredentials as $groupCredential) {
            $groupCredential->setAllowed($check);
        }

        $this->em->flush();
    }

    /**
     * @param GroupCredential[] $groupCredentials
     */
    public function allowStatusGroupCredentials(array $groupCredentials, bool $check): void
    {
        foreach ($groupCredentials as $groupCredential) {
            $groupCredential->setStatusAllowed($check);
        }

        $this->em->flush();
    }
}
