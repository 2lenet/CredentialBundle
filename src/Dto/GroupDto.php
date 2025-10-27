<?php

namespace Lle\CredentialBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GroupDto
{
    #[Assert\NotBlank]
    public string $name;

    public string $label = '';

    public bool $isRole = true;

    public bool $active = true;

    public string $requiredRole = '';

    public ?int $rank = null;
}
