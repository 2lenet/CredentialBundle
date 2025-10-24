<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Dto\InitProjectDto;
use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Factory\GroupCredentialFactory;
use Lle\CredentialBundle\Factory\GroupFactory;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class CredentialService
{
    public function __construct(
        #[AutowireIterator('credential.warmup')] protected iterable $warmuppers,
        protected EntityManagerInterface $em,
        protected ContainerBagInterface $container,
        protected HttpClientInterface $client,
        protected CredentialFactory $credentialFactory,
        protected GroupFactory $groupFactory,
        protected GroupCredentialFactory $groupCredentialFactory,
        protected CacheItemPoolInterface $cache,
    ) {
    }

    public function sendCredentials(): int
    {
        $credentials = [];
        foreach ($this->warmuppers as $warmup) {
            $credentials = array_merge($credentials, $warmup->warmup());
        }

        $projectUrl = $this->container->get('lle_credential.crudit_studio_url');
        $projectName = $this->container->get('lle_credential.project_name');

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

        return $response->getStatusCode();
    }

    public function initProject(): void
    {
        $projectUrl = $this->container->get('lle_credential.crudit_studio_url');
        $projectName = $this->container->get('lle_credential.project_name');

        $credentials = $this->em->getRepository(Credential::class)->findAllOrdered();
        $groups = $this->em->getRepository(Group::class)->findAllOrdered();
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findAll();

        $initProjectDto = $this->createInitProjectDto($credentials, $groups, $groupCredentials);

        $response = $this->client->request(
            'POST',
            $projectUrl . '/api/credential/init' . $projectName,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(
                    $initProjectDto,
                    JSON_PRETTY_PRINT
                )
            ]
        );

        $this->resetCache();
    }

    public function createInitProjectDto(array $credentials, array $groups, array $groupCredentials): InitProjectDto
    {
        $initProjectDto = new InitProjectDto();

        foreach ($credentials as $credential) {
            $credentialDto = $this->credentialFactory->createCredentialDto($credential);

            $initProjectDto->credentials[] = $credentialDto;
        }

        foreach ($groups as $group) {
            $groupDto = $this->groupFactory->createGroupDto($group);

            $initProjectDto->groups[] = $groupDto;
        }

        foreach ($groupCredentials as $groupCredential) {
            $groupCredentialDto = $this->groupCredentialFactory->createGroupCredentialDto($groupCredential);

            $initProjectDto->groupCredentials[] = $groupCredentialDto;
        }

        return $initProjectDto;
    }
}
