<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\CredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'credential:dump',
    description: 'Dump credential configuration',
)]
class CredentialDumpCommand extends Command
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private CredentialService $credentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $projectDir */
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $filename = $projectDir . '/config/credentials.json';

        $output->writeln("Dump Credentials to file $filename");

        $this->credentialService->dumpCredentials($filename);

        return Command::SUCCESS;
    }
}
