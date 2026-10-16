<?php

namespace Lle\CredentialBundle\Tests\Security;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Lle\CredentialBundle\Security\CredentialVoter;
use Lle\CredentialBundle\Service\CredentialServiceTrait;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Contracts\Translation\TranslatorInterface;

class CredentialVoterTest extends TestCase
{
    private const ROLE_VIEW = 'ROLE_ORDER_SHOW';
    private const ROLE_EDIT = 'ROLE_ORDER_EDIT';
    private const GROUP = 'EDITOR';

    private CacheItemPoolInterface $cache;

    /** @var array<int, array{string, bool}> */
    private array $rights = [];

    private int $databaseReads = 0;

    protected function setUp(): void
    {
        $this->cache = new ArrayAdapter();
        $this->rights = [[self::ROLE_VIEW, true], [self::ROLE_EDIT, false]];
        $this->databaseReads = 0;
    }

    public function testRightsAreNotReadAgainWhenNothingChanged(): void
    {
        $voter = $this->makeVoter();
        $voter->reset();

        $this->assertTrue($this->isGranted($voter, self::ROLE_VIEW));
        $this->assertSame(2, $this->databaseReads);
    }

    public function testRightsAreReloadedOnNextRequestAfterCacheReset(): void
    {
        $voter = $this->makeVoter();
        $this->assertFalse($this->isGranted($voter, self::ROLE_EDIT));

        $this->rights = [[self::ROLE_VIEW, true], [self::ROLE_EDIT, true]];
        $this->resetCredentialCache();
        $voter->reset();

        $this->assertTrue($this->isGranted($voter, self::ROLE_EDIT));
    }

    public function testRightsAreCheckedOnlyOncePerRequest(): void
    {
        $voter = $this->makeVoter();
        $voter->reset();
        $this->assertFalse($this->isGranted($voter, self::ROLE_EDIT));

        $this->rights = [[self::ROLE_VIEW, true], [self::ROLE_EDIT, true]];
        $this->resetCredentialCache();

        $this->assertFalse($this->isGranted($voter, self::ROLE_EDIT));
    }

    public function testRightsAreKeptWhenCacheIsResetWithoutNewRequest(): void
    {
        $voter = $this->makeVoter();
        $this->resetCredentialCache();

        $this->assertTrue($this->isGranted($voter, self::ROLE_VIEW));
    }

    private function resetCredentialCache(): void
    {
        $cache = $this->cache;
        $service = new class ($cache) {
            use CredentialServiceTrait;

            protected ParameterBagInterface $parameterBag;

            protected TranslatorInterface $translator;

            public function __construct(protected CacheItemPoolInterface $cache)
            {
            }
        };
        $service->resetCache();
    }

    private function isGranted(CredentialVoter $voter, string $role): bool
    {
        $token = new UsernamePasswordToken(new InMemoryUser('user', null, ['ROLE_' . self::GROUP]), 'main');

        return $voter->vote($token, null, [$role]) === CredentialVoter::ACCESS_GRANTED;
    }

    private function makeVoter(): CredentialVoter
    {
        $groupRepository = $this->createMock(EntityRepository::class);
        $groupRepository->method('findAll')->willReturnCallback(function (): array {
            $this->databaseReads++;
            $group = (new Group())->setName(self::GROUP);
            $groupCredentials = [];
            foreach ($this->rights as [$role, $allowed]) {
                $groupCredentials[] = (new GroupCredential())
                    ->setGroupe($group)
                    ->setCredential((new Credential())->setRole($role))
                    ->setAllowed($allowed);
            }

            return $groupCredentials;
        });

        $credentialRepository = $this->createMock(EntityRepository::class);
        $credentialRepository->method('findAll')->willReturnCallback(function (): array {
            $this->databaseReads++;

            return array_map(
                fn(array $right): Credential => (new Credential())->setRole($right[0]),
                $this->rights,
            );
        });

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(
            fn(string $class): EntityRepository => $class === GroupCredential::class
                ? $groupRepository
                : $credentialRepository
        );

        return new CredentialVoter($em, $this->cache);
    }
}
