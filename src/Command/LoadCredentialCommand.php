<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefined;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefined;
use Lle\CredentialBundle\Service\LoadCredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lle:credential:load',
    description: 'Load Credential configuration',
)]
class LoadCredentialCommand extends Command
{
    public function __construct(
        protected LoadCredentialService $loadCredentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->loadCredentialService->load();
        } catch (ConfigurationProjectCodeNotDefined | ConfigurationClientUrlNotDefined) {
            $output->writeln('<error>You must defined client configuration</error>');
        }

        return Command::SUCCESS;
    }
}
