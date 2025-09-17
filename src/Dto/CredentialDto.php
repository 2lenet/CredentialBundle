<?php

namespace Lle\CredentialBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CredentialDto
{
    #[Assert\NotBlank]
    public string $role;

    public ?string $rubrique = null;

    public ?string $libelle = null;

    public array $listeStatus = [];

    public bool $visible = true;

    public ?int $tri = null;
}