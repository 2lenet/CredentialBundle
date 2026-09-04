<?php

namespace Lle\CredentialBundle\Tests\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Factory\CredentialFactory;
use PHPUnit\Framework\TestCase;

class CredentialFactoryTest extends TestCase
{
    private CredentialFactory $credentialFactory;

    protected function setUp(): void
    {
        $this->credentialFactory = new CredentialFactory($this->createMock(EntityManagerInterface::class));
    }

    private function makeCredential(array $statusList): Credential
    {
        $credential = new Credential();
        $credential
            ->setRole('ROLE_COMMANDELIGNE_SUBLISTFIELD_PCATN')
            ->setSection('COMMANDELIGNE')
            ->setLabel('role.commandeligne.pcatn')
            ->setStatusList($statusList);

        return $credential;
    }

    public function testUpdateKeepsTheStatusListWhenTheWarmupDeclaresNone(): void
    {
        $credential = $this->makeCredential(['TARIFE@draft', 'NONTARIFE@draft']);

        $this->credentialFactory->update($credential, 'COMMANDELIGNE', 'role.commandeligne.pcatn');

        self::assertSame(['TARIFE@draft', 'NONTARIFE@draft'], $credential->getStatusList());
    }

    public function testUpdateReplacesTheStatusListWhenTheWarmupDeclaresOne(): void
    {
        $credential = $this->makeCredential(['TARIFE@draft']);

        $this->credentialFactory->update($credential, 'COMMANDELIGNE', 'role.commandeligne.pcatn', ['NEW@status']);

        self::assertSame(['NEW@status'], $credential->getStatusList());
    }

    public function testUpdateEmptiesTheStatusListWhenTheWarmupDeclaresAnEmptyOne(): void
    {
        $credential = $this->makeCredential(['TARIFE@draft']);

        $this->credentialFactory->update($credential, 'COMMANDELIGNE', 'role.commandeligne.pcatn', []);

        self::assertSame([], $credential->getStatusList());
    }

    public function testCreateStartsWithoutStatusWhenNoneIsDeclared(): void
    {
        $credential = $this->credentialFactory->create('ROLE_TEST', 'TEST', 'test');

        self::assertSame([], $credential->getStatusList());
    }
}
