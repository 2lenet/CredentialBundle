<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\RemoteApiException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SyncGroupService
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected NormalizerInterface $normalizer,
        protected ClientService $client,
    ) {
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function sync(): void
    {
        $groups = $this->em->getRepository(Group::class)->findAllOrdered();

        /** @var array<int, array<string, mixed>> $data */
        $data = $this->normalizer->normalize($groups, 'array', [
            'groups' => Group::GROUP_API_GROUP,
        ]);

        $this->client->syncGroups($data);
    }
}
