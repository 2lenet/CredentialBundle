<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;

class CredentialFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function create(
        string $role,
        ?string $section = null,
        ?string $label = null,
        ?array $statusList = null,
        bool $visible = true,
    ): Credential {
        $credential = new Credential();
        $credential
            ->setRole($role)
            ->setSection($section ?? $this->generateSection($role))
            ->setLabel($label ?? $role)
            ->setStatusList($statusList ?? [])
            ->setVisible($visible)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($credential);

        return $credential;
    }

    public function update(
        Credential $credential,
        ?string $section = null,
        ?string $label = null,
        ?array $statusList = null,
        bool $visible = true,
    ): Credential {
        $credential
            ->setSection($section ?? $this->generateSection((string)$credential->getRole()))
            ->setLabel($label ?? (string)$credential->getRole())
            ->setStatusList($statusList ?? [])
            ->setVisible($visible);

        return $credential;
    }

    public function createFromArray(array $credentialArray): Credential
    {
        $credential = new Credential();
        $credential
            ->setRole($credentialArray['role'])
            ->setSection($credentialArray['section'] ?? $this->generateSection($credentialArray['role']))
            ->setLabel($credentialArray['label'] ?? $credentialArray['role'])
            ->setStatusList($credentialArray['statusList'])
            ->setVisible($credentialArray['visible'])
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($credential);

        return $credential;
    }

    public function generateSection(string $role): string
    {
        $explodedRole = explode('_', $role);

        return strtoupper($explodedRole[1]);
    }
}
