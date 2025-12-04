<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Factory\GroupCredentialFactory;
use Lle\CredentialBundle\Factory\GroupFactory;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoadCredentialService
{
    use CredentialServiceTrait;

    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected EntityManagerInterface $em,
        protected CacheItemPoolInterface $cache,
        protected CredentialFactory $credentialFactory,
        protected GroupFactory $groupFactory,
        protected GroupCredentialFactory $groupCredentialFactory,
        protected ClientService $client,
        protected TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws ProjectNotFoundException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationClientUrlNotDefinedException
     */
    public function load(): void
    {
        $credentials = $this->client->load();

        $this->em->getRepository(GroupCredential::class)->createQueryBuilder('g')->delete()->getQuery()->execute();
        $this->em->getRepository(Credential::class)->createQueryBuilder('c')->delete()->getQuery()->execute();

        $this->loadCredentials($credentials['credentials']);
        $this->loadGroups($credentials['groups']);
        $this->loadGroupCredentials($credentials['group_credentials']);

        $this->resetCache();
    }

    public function loadCredentials(array $credentials): void
    {
        foreach ($credentials as $credential) {
            $this->credentialFactory->createFromArray($credential);
        }

        $this->em->flush();
    }

    public function loadGroups(array $groups): void
    {
        foreach ($groups as $group) {
            $this->groupFactory->createFromArray($group);
        }
    }

    public function loadGroupCredentials(array $groupCredentials): void
    {
        foreach ($groupCredentials as $groupCredential) {
            $this->groupCredentialFactory->createFromArray($groupCredential);
        }

        $this->em->flush();
    }
}
