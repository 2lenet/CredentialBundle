<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\CredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lle:credential:warmup',
    description: 'Initialise Credential list',
)]
class CredentialWarmupCommand extends Command
{
    public function __construct(
        protected CredentialService $credentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Warmup Credential');

        $response = $this->credentialService->sendCredentials();
//        if ($response === 200) {
//            $this->credentialService->loadCredentials();
//        }

        return Command::SUCCESS;
    }
}
