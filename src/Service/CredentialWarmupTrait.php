<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

trait CredentialWarmupTrait
{
    protected function checkAndCreateCredential(string $role, string $rubrique, string $libelle, int $tri): void
    {
        $cred = $this->credentialRepository->findOneBy(['role' => $role]);
        if ( $cred == null) {
            $cred = new Credential();
            $cred->setRole($role);
        }
        $cred->setLibelle($libelle);
        $cred->setRubrique($rubrique);
        $cred->setVisible(true);
        $cred->setTri($tri);
        $this->entityManager->persist($cred);
        $this->entityManager->flush();
    }

}
