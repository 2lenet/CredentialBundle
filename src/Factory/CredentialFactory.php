<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Dto\CredentialDto;
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

    public function createFromDto(CredentialDto $credentialDto): Credential
    {
        $credential = new Credential();
        $credential
            ->setRole($credentialDto->role)
            ->setSection($credentialDto->rubrique ?? $this->generateSection($credentialDto->role))
            ->setLabel($credentialDto->libelle ?? $credentialDto->role)
            ->setStatusList($credentialDto->statusList)
            ->setVisible($credentialDto->visible)
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
