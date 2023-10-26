<?php

namespace Lle\CredentialBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand(
    name: 'credential:warmup',
    description: 'Initialise Credential list',
)]
class CredentialWarmupCommand extends Command
{

    public function __construct(#[TaggedIterator('credential.warmup')] protected iterable $warmuppers)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Warmup Credential");
        foreach ($this->warmuppers as $warmup) {
            $output->writeln("\n** Warmuppper ". get_class($warmup)." ** \n");
            $warmup->warmup();
        }
        return Command::SUCCESS;
    }
}
