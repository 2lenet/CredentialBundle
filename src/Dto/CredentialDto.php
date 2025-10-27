<?php

namespace Lle\CredentialBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CredentialDto
{
    #[Assert\NotBlank]
    public string $role;

    public ?string $section = null;

    public ?string $label = null;

    public array $statusList = [];

    public bool $visible = true;
}
