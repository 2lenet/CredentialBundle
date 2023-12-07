<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\CredentialService;
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
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $this->parameterBag->get('kernel.project_dir') . '/config/credentials.json';
        $output->writeln("Load Credentials from file $filename");

        $this->credentialService->loadCredentials($filename);

        return Command::SUCCESS;
    }
}
