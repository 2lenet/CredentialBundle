<?php

namespace Lle\CredentialBundle\Dto;

class InitProjectDto
{
    /** @var CredentialDto[] $credentials */
    public array $credentials;

    /** @var GroupDto[] $groups */
    public array $groups;

    /** @var GroupCredentialDto[] $groupCredentials */
    public array $groupCredentials;
}