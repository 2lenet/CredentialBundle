<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Lle\CredentialBundle\Exception\ProjectAlreadyInitializedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class InitCredentialService
{
    use CredentialServiceTrait;

    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected EntityManagerInterface $em,
        protected NormalizerInterface $normalizer,
        protected ClientService $client,
        protected CacheItemPoolInterface $cache,
        protected TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws ProjectAlreadyInitializedException
     * @throws ProjectNotFoundException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationClientUrlNotDefinedException
     */
    public function init(): void
    {
        $credentials = $this->getCredentials($this->em->getRepository(Credential::class)->findAllOrdered());
        $groups = $this->em->getRepository(Group::class)->findAllOrdered();
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findAll();

        $data = [
            'credentials' => $credentials,
            'groups' => $this->normalizer->normalize($groups, 'array', [
                'groups' => Group::GROUP_API_GROUP,
            ]),
            'groupCredentials' => $this->normalizer->normalize($groupCredentials, 'array', [
                'groups' => GroupCredential::GROUPCREDENTIAL_API_GROUP,
            ]),
        ];

        $this->client->init($data);

        $this->resetCache();
    }
}
