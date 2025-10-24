<?php

namespace Lle\CredentialBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WarmupCredentialService
{
    use CredentialServiceTrait;

    public function __construct(
        #[AutowireIterator('credential.warmup')] protected iterable $warmuppers,
        protected ParameterBagInterface $parameterBag,
    ) {
    }

    public function warmup(): void
    {
        $credentials = [];
        foreach ($this->warmuppers as $warmup) {
            $credentials = array_merge($credentials, $warmup->warmup());
        }

        $projectUrl = $this->parameterBag->get('lle_credential.client_url');
        $projectName = $this->parameterBag->get('lle_credential.project_code');

        $response = $this->client->request(
            'POST',
            $projectUrl . '/api/credential/warmup/' . $projectName,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($credentials)
            ]
        );

        $this->resetCache();
    }
}
