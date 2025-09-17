<?php

namespace Lle\CredentialBundle\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GroupCredentialDto
{
    #[Assert\NotBlank]
    public string $groupName;

    #[Assert\NotBlank]
    public string $credentialRole;

    public bool $allowed = true;

    public bool $statusAllowed = false;
}