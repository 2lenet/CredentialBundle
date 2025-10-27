<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Dto\CredentialDto;
use Lle\CredentialBundle\Dto\GroupCredentialDto;
use Lle\CredentialBundle\Dto\GroupDto;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Factory\GroupCredentialFactory;
use Lle\CredentialBundle\Factory\GroupFactory;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
    ) {
    }

    /**
     * @throws ProjectNotFoundException
     */
    public function load(): void
    {
        $credentials = $this->client->load();

        $this->em->getRepository(Group::class)->createQueryBuilder('g')->delete()->getQuery()->execute();
        $this->em->getRepository(Credential::class)->createQueryBuilder('c')->delete()->getQuery()->execute();
        $this->em->getRepository(GroupCredential::class)->createQueryBuilder('g')->delete()->getQuery()->execute();

        $this->loadCredentials($credentials['credentials']);
        $this->loadGroups($credentials['groups']);
        $this->loadGroupCredentials($credentials['group_credentials']);

        $this->resetCache();
    }

    /**
     * @param CredentialDto[] $credentialDtos
     */
    public function loadCredentials(array $credentialDtos): void
    {
        foreach ($credentialDtos as $credentialDto) {
            $this->credentialFactory->createFromDto($credentialDto);
        }
    }

    /**
     * @param GroupDto[] $groupDtos
     */
    public function loadGroups(array $groupDtos): void
    {
        foreach ($groupDtos as $groupDto) {
            $this->groupFactory->createFromDto($groupDto);
        }
    }

    /**
     * @param GroupCredentialDto[] $groupCredentialDtos
     */
    public function loadGroupCredentials(array $groupCredentialDtos): void
    {
        foreach ($groupCredentialDtos as $groupCredentialDto) {
            $this->groupCredentialFactory->createFromDto($groupCredentialDto);
        }
    }
}
