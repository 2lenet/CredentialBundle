<?php

namespace Lle\CredentialBundle\Service;

use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\ProjectAlreadyInitializedException;
use Lle\CredentialBundle\Exception\RemoteApiException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ClientService
{
    private ?string $clientUrl;
    private ?string $projectCode;
    private ?string $projectToken;

    public function __construct(
        protected ParameterBagInterface $parameterBag,
        protected HttpClientInterface $client,
        protected NormalizerInterface $normalizer,
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
        $this->projectToken = $projectToken ? 'Bearer ' . $projectToken : $projectToken;
    }

    /**
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws RemoteApiException
     * @throws ConfigurationProjectTokenNotDefinedException
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

        $this->throwIfRemoteError($response);

        return json_decode($response->getContent(false), true);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function warmup(array $credentials): void
    {
        if (!$this->shouldCallRemote()) {
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

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ProjectAlreadyInitializedException
     * @throws RemoteApiException
     * @throws ConfigurationProjectTokenNotDefinedException
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

        try {
            if ($response->getStatusCode() === Response::HTTP_BAD_REQUEST) {
                throw new ProjectAlreadyInitializedException($response->getContent(false));
            }
        } catch (TransportExceptionInterface $e) {
            throw new RemoteApiException($e->getMessage());
        }

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function toggleGroup(Group $group, bool $check): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/project/toggle-group/' . $this->projectCode . '/' . $group->getName() . '/' . ($check ? 1 : 0),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
                ],
            ]
        );

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function toggleSection(string $section, Group $group, bool $check): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
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

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function toggleCredential(Credential $credential, Group $group, bool $check): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
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

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function allowStatus(Credential $credential, Group $group, bool $check): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
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

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function allowForStatus(Credential $credential, Group $group, string $status, bool $check): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
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

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function createGroup(Group $group): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/project/group/create/' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
                ],
                'body' => json_encode($this->normalizer->normalize($group, 'array', [
                    'groups' => Group::GROUP_API_GROUP,
                ])),
            ]
        );

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function editGroup(string $oldName, Group $group): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/project/group/edit/' . $this->projectCode . '/' . $oldName,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
                ],
                'body' => json_encode($this->normalizer->normalize($group, 'array', [
                    'groups' => Group::GROUP_API_GROUP,
                ])),
            ]
        );

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function deleteGroup(Group $group): void
    {
        if (!$this->shouldCallRemote()) {
            return;
        }

        $response = $this->client->request(
            'DELETE',
            $this->clientUrl . '/api/project/group/delete/' . $this->projectCode . '/' . $group->getName(),
            [
                'headers' => [
                    'Authorization' => $this->projectToken,
                ],
            ]
        );

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     * @throws RemoteApiException
     */
    public function syncGroups(array $groups): void
    {
        $this->checkClientConfig();

        $response = $this->client->request(
            'POST',
            $this->clientUrl . '/api/project/group/sync/' . $this->projectCode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $this->projectToken,
                ],
                'body' => json_encode($groups),
            ]
        );

        $this->throwIfRemoteError($response);
    }

    /**
     * @throws RemoteApiException
     */
    private function throwIfRemoteError(ResponseInterface $response): void
    {
        try {
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                $body = json_decode($response->getContent(false), true) ?? [];
                throw new RemoteApiException($body['message'] ?? 'Remote API error.');
            }
        } catch (TransportExceptionInterface $e) {
            throw new RemoteApiException($e->getMessage());
        }
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

    /**
     * Returns false if no remote config is set at all (remote is optional).
     * Throws if config is partially set (misconfiguration).
     * Returns true if all config is present and valid.
     *
     * @throws ConfigurationClientUrlNotDefinedException
     * @throws ConfigurationProjectCodeNotDefinedException
     * @throws ConfigurationProjectTokenNotDefinedException
     */
    private function shouldCallRemote(): bool
    {
        if (!$this->clientUrl && !$this->projectCode && !$this->projectToken) {
            return false;
        }

        $this->checkClientConfig();

        return true;
    }
}
