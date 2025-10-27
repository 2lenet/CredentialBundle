<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefined;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefined;
use Lle\CredentialBundle\Service\InitCredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lle:credential:init-project',
    description: 'Initialize a project',
)]
class InitProjectCommand extends Command
{
    public function __construct(
        protected InitCredentialService $initCredentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->initCredentialService->init();
        } catch (ConfigurationProjectCodeNotDefined | ConfigurationClientUrlNotDefined) {
            $output->writeln('<error>You must defined client configuration</error>');
        }

        return Command::SUCCESS;
    }
}
