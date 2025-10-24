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
        ?string $rubrique = null,
        ?string $libelle = null,
        ?array $listeStatus = null,
        bool $visible = true,
        ?int $tri = null,
    ): Credential {
        $credential = new Credential();
        $credential
            ->setRole($role)
            ->setRubrique($rubrique ?? $this->generateRubrique($role))
            ->setLibelle($libelle ?? $role)
            ->setListeStatus($listeStatus ?? [])
            ->setVisible($visible)
            ->setTri($tri ?? $this->getTri());

        $this->em->persist($credential);

        return $credential;
    }

    public function createFromDto(CredentialDto $credentialDto): Credential
    {
        $credential = new Credential();
        $credential
            ->setRole($credentialDto->role)
            ->setRubrique($credentialDto->rubrique ?? $this->generateRubrique($credentialDto->role))
            ->setLibelle($credentialDto->libelle ?? $credentialDto->role)
            ->setListeStatus($credentialDto->listeStatus)
            ->setVisible($credentialDto->visible)
            ->setTri($credentialDto->tri ?? $this->getTri());

        $this->em->persist($credential);

        return $credential;
    }

    public function generateRubrique(string $role): string
    {
        $explodedRole = explode('_', $role);

        return strtoupper($explodedRole[1]);
    }

    public function getTri(): int
    {
        $lastCredential = $this->em->getRepository(Credential::class)->findOneBy([], ['tri' => 'DESC']);
        if ($lastCredential) {
            return $lastCredential->getTri() + 1;
        }

        return 0;
    }
}
