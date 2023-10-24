<?php

namespace Lle\CredentialBundle\Contracts;

interface CredentialWarmupInterface
{
    public function warmUp(): void;
}
