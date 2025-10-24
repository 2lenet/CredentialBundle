<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InitCredentialService
{
    use CredentialServiceTrait;

    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected EntityManagerInterface $em,
        protected NormalizerInterface $normalizer,
    ) {
    }

    public function init(): void
    {
        $clientUrl = $this->parameterBag->get('lle_credential.client_url');
        $projectCode = $this->parameterBag->get('lle_credential.project_code');

        $credentials = $this->em->getRepository(Credential::class)->findAllOrdered();
        $groups = $this->em->getRepository(Group::class)->findAllOrdered();
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findAll();

        $data = [
            'credentials' => $this->normalizer->normalize($credentials, 'array', [
                'groups' => Credential::CREDENTIAL_API_GROUP
            ]),
            'groups' => $this->normalizer->normalize($groups, 'array', [
                'groups' => Group::GROUP_API_GROUP
            ]),
            'groupCredentials' => $this->normalizer->normalize($groupCredentials, 'array', [
                'groups' => GroupCredential::GROUPCREDENTIAL_API_GROUP
            ]),
        ];

        $response = $this->client->request(
            'POST',
            $clientUrl . '/api/credential/init' . $projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($data),
            ]
        );

        $this->resetCache();
    }
}
