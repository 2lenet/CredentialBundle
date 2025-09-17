<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Dto\CredentialDto;
use Lle\CredentialBundle\Entity\Credential;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

trait CredentialWarmupTrait
{
    protected function getCredentials(
        string $role,
        ?string $rubrique,
        ?string $libelle,
        ?array $listeStatus = [],
        ?bool $visible = true
    ): CredentialDto {
        $dto = new CredentialDto();
        $dto->role = $role;
        $dto->libelle = $libelle;
        $dto->rubrique = $rubrique;
        $dto->listeStatus = $listeStatus;
        $dto->visible = $visible;

        return $dto;
    }
}
