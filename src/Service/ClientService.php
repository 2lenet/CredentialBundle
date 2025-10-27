<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefined;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefined;
use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Lle\CredentialBundle\Exception\ProjectAlreadyInitializedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientService
{
    private ?string $clientUrl;
    private ?string $projectCode;

    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected HttpClientInterface   $client,
    )
    {
        $this->clientUrl = $this->parameterBag->get('lle_credential.client_url');
        $this->projectCode = $this->parameterBag->get('lle_credential.project_code');
    }

    /**
     * @throws ConfigurationProjectCodeNotDefined
     * @throws ConfigurationClientUrlNotDefined
     * @throws ProjectNotFoundException
     */
    public function load(): array
    {
        $this->checkClientConfig();

        $response = $this->client->request(
            'GET',
            $this->clientUrl . '/api/credential/pull/' . $this->projectCode
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            throw new ProjectNotFoundException($response->getContent());
        }

        return json_decode($response->getContent());
    }

    /**
     * @throws ProjectNotFoundException
     */
    public function warmup(array $credentials): void
    {
        if (!$this->hasClientConfig()) {
            return;
        }

        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/credential/warmup/' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($credentials)
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            throw new ProjectNotFoundException($response->getContent());
        }
    }

    /**
     * @throws ConfigurationProjectCodeNotDefined
     * @throws ConfigurationClientUrlNotDefined
     * @throws ProjectAlreadyInitializedException
     * @throws ProjectNotFoundException
     */
    public function init(array $data): void
    {
        $this->checkClientConfig();

        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/credential/init' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(
                    $data,
                    JSON_PRETTY_PRINT
                )
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            throw new ProjectNotFoundException($response->getContent());
        }

        if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            throw new ProjectAlreadyInitializedException($response->getContent());
        }
    }

    public function toggleGroup(Group $group, bool $check): void
    {
        if (!$this->hasClientConfig()) {
            return;
        }

        $this->client->request(
            'POST',
            $this->clientUrl . '/api/project/toggle-group/' . $this->projectCode . '/' . $group->getName() . '/' . $check,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    public function toggleRubrique(string $rubrique, Group $group, bool $check): void
    {
        if (!$this->hasClientConfig()) {
            return;
        }

        $this->client->request(
            'POST',
            $this->clientUrl
            . '/api/project/toggle-rubrique/'
            . $this->projectCode
            . '/'
            . $rubrique
            . '/'
            . $group->getName()
            . '/'
            . $check,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    public function toggleCredential(Credential $credential, Group $group, bool $check): void
    {
        if (!$this->hasClientConfig()) {
            return;
        }

        $this->client->request(
            'POST',
            $this->clientUrl
            . '/api/project/toggle-credential/'
            . $this->projectCode
            . '/'
            . $credential->getRole()
            . '/'
            . $group->getName()
            . '/'
            . $check,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    public function allowStatus(Credential $credential, Group $group, bool $check): void
    {
        if (!$this->hasClientConfig()) {
            return;
        }

        $this->client->request(
            'POST',
            $this->clientUrl
            . '/api/project/allow-status/'
            . $this->projectCode
            . '/'
            . $credential->getRole()
            . '/'
            . $group->getName()
            . '/'
            . $check,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    public function allowForStatus(Credential $credential, Group $group, string $status, bool $check): void
    {
        if (!$this->hasClientConfig()) {
            return;
        }

        $this->client->request(
            'POST',
            $this->clientUrl
            . '/api/project/allow-for-status/'
            . $this->projectCode
            . '/'
            . $credential->getRole()
            . '/'
            . $group->getName()
            . '/'
            . $status
            . '/'
            . $check,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    public function checkClientConfig(): void
    {
        if (!$this->clientUrl) {
            throw new ConfigurationClientUrlNotDefined();
        }
        if (!$this->projectCode) {
            throw new ConfigurationProjectCodeNotDefined();
        }
    }

    public function hasClientConfig(): bool
    {
        if (!$this->clientUrl || !$this->projectCode) {
            return false;
        }

        return true;
    }
}
