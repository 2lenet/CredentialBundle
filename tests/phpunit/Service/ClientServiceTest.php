<?php

namespace Lle\CredentialBundle\Tests\Service;

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

    private function makeGroup(): Group
    {
        $group = new Group();
        $group->setName('SUPER_ADMIN');

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
}
