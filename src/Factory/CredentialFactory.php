<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Dto\CredentialDto;
use Lle\CredentialBundle\Entity\Credential;

class CredentialFactory
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function createCredentials(
        string $role,
        ?string $rubrique = null,
        ?string $libelle = null,
        ?array $listeStatus = null,
        ?bool $visible = null,
        ?int $tri
    ): Credential {
        /** @var ?Credential $cred */
        $cred = $this->em->getRepository(Credential::class)->findOneBy(['role' => $role]);

        if (!$cred) {
            $cred = new Credential();
            $cred->setCreatedAt(new \DateTimeImmutable());
            $cred->setRole($role);
            $cred->setRubrique("");
        }

        if ($libelle) {
            $cred->setLibelle($libelle);
        } elseif ($cred->getRole()) {
            $cred->setLibelle($cred->getRole());
        }

        if ($rubrique) {
            $cred->setRubrique($rubrique);
        } elseif ($cred->getRole()) {
            $cred->setRubrique($this->generateRubrique($role));
        }
        if ($visible) {
            $cred->setVisible($visible);
        }
        if ($listeStatus) {
            $cred->setListeStatus($listeStatus);
        }

        if (!$tri) {
            $lastCredential = $this->em->getRepository(Credential::class)->findByLatestTri();
            if ($lastCredential) {
                $cred->setTri($lastCredential->getTri() + 1);
            } else {
                $cred->setTri(0);
            }
        } else {
            $cred->setTri($tri);
        }

        $cred->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($cred);
        $this->em->flush();

        return $cred;
    }

    public function generateRubrique(string $role): string
    {
        $string = explode('_', $role);

        return strtoupper($string[1]);
    }

    public function createCredentialDto(Credential $credential): CredentialDto
    {
        $credentialDto = new CredentialDto();
        $credentialDto->role = $credential->getRole();
        $credentialDto->rubrique = $credential->getRubrique();
        $credentialDto->libelle = $credential->getLibelle();
        $credentialDto->listeStatus = $credential->getListeStatus();
        $credentialDto->visible = $credential->isVisible();
        $credentialDto->tri = $credential->getTri();

        return $credentialDto;
    }
}
