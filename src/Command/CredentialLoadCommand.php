<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\CredentialService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'credential:load',
    description: 'Load Credential configuration',
)]
class CredentialLoadCommand extends Command
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private CredentialService $credentialService,
        private CacheItemPoolInterface $cache,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $projectDir */
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $filename = $projectDir . '/config/credentials.json';

        $output->writeln("Load Credentials from file $filename");

        $this->credentialService->loadCredentials($filename);

        if ($this->cache->hasItem('group_credentials')) {
            $this->cache->deleteItem('group_credentials');
        }

        if ($this->cache->hasItem('all_credentials')) {
            $this->cache->deleteItem('all_credentials');
        }

        return Command::SUCCESS;
    }
}
