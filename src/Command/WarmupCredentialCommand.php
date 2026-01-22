<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\WarmupCredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lle:credential:warmup',
    description: 'Update credentials list',
)]
class WarmupCredentialCommand extends Command
{
    public function __construct(
        protected WarmupCredentialService $warmupCredentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->warmupCredentialService->warmup();

        return Command::SUCCESS;
    }
}
