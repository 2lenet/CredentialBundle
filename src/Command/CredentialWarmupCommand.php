<?php

namespace Lle\CredentialBundle\Command;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand(
    name: 'credential:warmup',
    description: 'Initialise Credential list',
)]
class CredentialWarmupCommand extends Command
{
    public function __construct(
        #[AutowireIterator('credential.warmup')] protected iterable $warmuppers,
        private CacheItemPoolInterface $cache,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Warmup Credential");
        foreach ($this->warmuppers as $warmup) {
            $output->writeln("\n** Warmuppper " . get_class($warmup) . " ** \n");
            $warmup->warmup();
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
