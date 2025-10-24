<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\CredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lle:credential:load',
    description: 'Load Credential configuration',
)]
class CredentialLoadCommand extends Command
{
    public function __construct(
        protected CredentialService $credentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->credentialService->loadCredentials();
        $output->writeln("Load Credential from crudit-studio");

        return Command::SUCCESS;
    }
}
