<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Entity\Credential;

trait CredentialWarmupTrait
{
    protected function checkAndCreateCredential(
        string $role,
        string $rubrique,
        ?string $libelle,
        ?int $tri,
        ?array $listeStatus = null,
        ?bool $visible = true,
        ?string $type = null,
    ): void {
        $credential = $this->credentialRepository->findOneBy(['role' => $role]);
        if (!$credential) {
            echo "not found $role / $libelle\n";
            $credential = new Credential();
            $credential
                ->setRole($role)
                ->setTri(0)
                ->setCreatedAt(new \DateTimeImmutable());
        }

        $credential
            ->setRubrique($rubrique)
            ->setLibelle($libelle)
            ->setType($type)
            ->setVisible($visible ?? true);

        if ($listeStatus) {
            $credential->setListeStatus($listeStatus);
        }

        if ($tri) {
            $credential->setTri($tri);
        }

        $this->entityManager->persist($credential);
        $this->entityManager->flush();
    }
}
