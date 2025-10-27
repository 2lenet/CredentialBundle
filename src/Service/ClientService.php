<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\ProjectNotFoundException;
use Lle\CredentialBundle\Exception\ProjectAlreadyInitializedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClientService
{
    private ?string $clientUrl;
    private ?string $projectCode;
    private ?string $projectToken;

    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected HttpClientInterface $client,
    )
    {
        /** @var ?string $clientUrl */
        $clientUrl = $this->parameterBag->get('lle_credential.client_url');
        $this->clientUrl = $clientUrl;

        /** @var ?string $projectCode */
        $projectCode = $this->parameterBag->get('lle_credential.project_code');
        $this->projectCode = $projectCode;

        /** @var ?string $projectToken */
        $projectToken = $this->parameterBag->get('lle_credential.project_token');
        $this->projectToken = 'Bearer ' . $projectToken;
    }

    /**
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ProjectNotFoundException
     */
    public function load(): array
    {
        $this->checkClientConfig();

        $response = $this->client->request(
            'GET',
            $this->clientUrl . '/api/project/pull/' . $this->projectCode,
            [
                'headers' => [
                    'Authorization' => $this->projectToken,
                ]
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            throw new ProjectNotFoundException($response->getContent());
        }

        return json_decode($response->getContent(), true);
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
            $this->clientUrl . '/api/project/warmup/' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
                ],
                'body' => json_encode($credentials)
            ]
        );

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            throw new ProjectNotFoundException($response->getContent());
        }
    }

    /**
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ProjectAlreadyInitializedException
     * @throws ProjectNotFoundException
     */
    public function init(array $data): void
    {
        $this->checkClientConfig();

        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/project/init/' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
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
            $this->clientUrl . '/api/project/toggle-group/' . $this->projectCode . '/' . $group->getName() . '/' . ($check ? 1 : 0),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
                ],
            ]
        );
    }

    public function toggleSection(string $section, Group $group, bool $check): void
    {
        if (!$this->hasClientConfig()) {
            return;
        }

        $this->client->request(
            'POST',
            $this->clientUrl
            . '/api/project/toggle-section/'
            . $this->projectCode
            . '/'
            . $section
            . '/'
            . $group->getName()
            . '/'
            . ($check ? 1 : 0),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
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
            . ($check ? 1 : 0),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
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
            . ($check ? 1 : 0),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
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
            . ($check ? 1 : 0),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
                ],
            ]
        );
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     */
    public function checkClientConfig(): void
    {
        if (!$this->clientUrl) {
            throw new ConfigurationClientUrlNotDefinedException();
        }

        if (!$this->projectCode) {
            throw new ConfigurationProjectCodeNotDefinedException();
        }

        if (!$this->projectToken) {
            throw new ConfigurationProjectTokenNotDefinedException();
        }
    }

    public function hasClientConfig(): bool
    {
        if (!$this->clientUrl || !$this->projectCode || !$this->projectToken) {
            return false;
        }

        return true;
    }
}
