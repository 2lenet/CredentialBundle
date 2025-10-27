<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class WarmupCredentialService
{
    use CredentialServiceTrait;

    public function __construct(
        #[AutowireIterator('credential.warmup')] protected iterable $warmuppers,
        protected ParameterBagInterface $parameterBag,
        protected ClientService $client,
        protected EntityManagerInterface $em,
        protected NormalizerInterface $normalizer,
        protected CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @throws ProjectNotFoundException
     */
    public function warmup(): void
    {
        foreach ($this->warmuppers as $warmup) {
            $warmup->warmup();
        }

        $credentials = $this->em->getRepository(Credential::class)->findAll();
        $this->client->warmup($this->normalizer->normalize($credentials, 'array', [
            'groups' => Credential::CREDENTIAL_API_GROUP,
        ]));

        $this->resetCache();
    }
}
