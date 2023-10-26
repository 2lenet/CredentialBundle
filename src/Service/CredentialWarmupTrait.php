<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

trait CredentialWarmupTrait
{
    protected function checkAndCreateCredential(
        string $role,
        ?string $rubrique,
        ?string $libelle,
        ?int $tri,
        ?array $listeStatus = null,
    ): void {
        $cred = $this->credentialRepository->findOneBy(['role' => $role]);
        if ($cred === null) {
            echo "not found $role  / $libelle\n";
            $cred = new Credential();
            $cred->setRole($role);
            $cred->setRubrique("");
            $cred->setTri(0);
        }
        if ($libelle !== null) {
            $cred->setLibelle($libelle);
        }
        if ($rubrique !== null) {
            $cred->setRubrique($rubrique);
        }
        $cred->setVisible(true);
        if ($listeStatus !== null) {
            $cred->setListeStatus($listeStatus);
        }
        if ($tri !== null) {
            $cred->setTri($tri);
        }
        $this->entityManager->persist($cred);
        $this->entityManager->flush();
    }
}
