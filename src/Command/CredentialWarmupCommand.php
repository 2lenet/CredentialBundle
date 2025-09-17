<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\CredentialService;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsCommand(
    name: 'credential:warmup',
    description: 'Initialise Credential list',
)]
class CredentialWarmupCommand extends Command
{
    public function __construct(
        #[AutowireIterator('credential.warmup')] protected iterable $warmuppers,
        private CacheItemPoolInterface $cache,
        protected CredentialService $credentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $results = [];
        $output->writeln("Warmup Credential");
        foreach ($this->warmuppers as $warmup) {
            $output->writeln("\n** Warmuppper " . get_class($warmup) . " ** \n");
            $results = array_merge($results, $warmup->warmup());
        }

        $response = $this->credentialService->sendCredentials($results);
        if ($response === 200) {
            $this->credentialService->loadCredentials();
        }

        if ($this->cache->hasItem('group_credentials')) {
            $this->cache->deleteItem('group_credentials');
        }

        if ($this->cache->hasItem('all_credentials')) {
            $this->cache->deleteItem('all_credentials');
        }

        return Command::SUCCESS;
    }
}
