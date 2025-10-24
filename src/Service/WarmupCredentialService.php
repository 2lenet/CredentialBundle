<?php

namespace Lle\CredentialBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Lle\CredentialBundle\Service\ClientService;

class WarmupCredentialService
{
    use CredentialServiceTrait;

    public function __construct(
        #[AutowireIterator('credential.warmup')] protected iterable $warmuppers,
        protected ParameterBagInterface $parameterBag,
        protected ClientService $client,
    ) {
    }

    /**
     * @throws ProjectNotFoundException
     */
    public function warmup(): void
    {
        $credentials = [];
        foreach ($this->warmuppers as $warmup) {
            $credentials = array_merge($credentials, $warmup->warmup());
        }

        $this->client->warmup($credentials);

        $this->resetCache();
    }
}
