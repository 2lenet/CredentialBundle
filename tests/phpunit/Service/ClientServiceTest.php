<?php

namespace Lle\CredentialBundle\Tests\Service;

use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Service\ClientService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Lle\CredentialBundle\Exception\RemoteApiException;

class ClientServiceTest extends TestCase
{
    private function makeClientService(string $environment, MockHttpClient $httpClient): ClientService
    {
        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->method('normalize')->willReturn(['name' => 'SUPER_ADMIN']);

        return new ClientService(
            new ParameterBag([
                'lle_credential.client_url' => 'https://crudit-studio.example',
                'lle_credential.project_code' => 'PROJECT',
                'lle_credential.project_token' => 'token',
            ]),
            $httpClient,
            $normalizer,
            $this->createMock(Security::class),
            new RequestStack(),
            $environment,
        );
    }

    private function makeGroup(string $name = 'SUPER_ADMIN'): Group
    {
        $group = new Group();
        $group->setName($name);

        return $group;
    }

    public function testCreateGroupDoesNotReachTheRemoteFromTheTestEnvironment(): void
    {
        $httpClient = new MockHttpClient();

        $this->makeClientService('test', $httpClient)->createGroup($this->makeGroup());

        self::assertSame(0, $httpClient->getRequestsCount());
    }

    public function testWarmupDoesNotReachTheRemoteFromTheTestEnvironment(): void
    {
        $httpClient = new MockHttpClient();

        $this->makeClientService('test', $httpClient)->warmup([]);

        self::assertSame(0, $httpClient->getRequestsCount());
    }

    public function testCreateGroupReachesTheRemoteFromAnyOtherEnvironment(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{}'));

        $this->makeClientService('prod', $httpClient)->createGroup($this->makeGroup());

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    public function testSyncGroupsRefusesToReachTheRemoteFromTheTestEnvironment(): void
    {
        $httpClient = new MockHttpClient();

        self::expectException(RemoteApiException::class);

        try {
            $this->makeClientService('test', $httpClient)->syncGroups([]);
        } finally {
            self::assertSame(0, $httpClient->getRequestsCount());
        }
    }

    public function testSyncGroupsReachesTheRemoteFromAnyOtherEnvironment(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{}'));

        $this->makeClientService('prod', $httpClient)->syncGroups([]);

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    public function testLoadStillReachesTheRemoteFromTheTestEnvironment(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('{}'));

        $this->makeClientService('test', $httpClient)->load();

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    public function testToggleGroupSendsTheGroupNameInTheQueryString(): void
    {
        $requestedUrl = null;
        $httpClient = new MockHttpClient(function (string $method, string $url) use (&$requestedUrl) {
            $requestedUrl = $url;

            return new MockResponse('{}');
        });

        $this->makeClientService('prod', $httpClient)->toggleGroup($this->makeGroup('admin/local'), true);

        self::assertSame(
            'https://crudit-studio.example/api/project/toggle-group/PROJECT/1?groupName=admin%2Flocal',
            $requestedUrl,
        );
    }

    public function testAllowForStatusSendsTheGroupNameInTheQueryString(): void
    {
        $requestedUrl = null;
        $httpClient = new MockHttpClient(function (string $method, string $url) use (&$requestedUrl) {
            $requestedUrl = $url;

            return new MockResponse('{}');
        });

        $credential = new Credential();
        $credential->setRole('ROLE_C');

        $this->makeClientService('prod', $httpClient)
            ->allowForStatus($credential, $this->makeGroup('admin/local'), 'A', false);

        self::assertSame(
            'https://crudit-studio.example/api/project/allow-for-status/PROJECT/ROLE_C/A/0?groupName=admin%2Flocal',
            $requestedUrl,
        );
    }

    public function testDeleteGroupSendsTheGroupNameInTheQueryString(): void
    {
        $requestedUrl = null;
        $httpClient = new MockHttpClient(function (string $method, string $url) use (&$requestedUrl) {
            $requestedUrl = $url;

            return new MockResponse('{}');
        });

        $this->makeClientService('prod', $httpClient)->deleteGroup($this->makeGroup('admin/local'));

        self::assertSame(
            'https://crudit-studio.example/api/project/group/delete/PROJECT?groupName=admin%2Flocal',
            $requestedUrl,
        );
    }

    public function testEditGroupSendsTheOldGroupNameInTheQueryString(): void
    {
        $requestedUrl = null;
        $httpClient = new MockHttpClient(function (string $method, string $url) use (&$requestedUrl) {
            $requestedUrl = $url;

            return new MockResponse('{}');
        });

        $this->makeClientService('prod', $httpClient)->editGroup('admin/local', $this->makeGroup('admin/remote'));

        self::assertSame(
            'https://crudit-studio.example/api/project/group/edit/PROJECT?groupName=admin%2Flocal',
            $requestedUrl,
        );
    }
}
