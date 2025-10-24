<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Service\CredentialService;
use Lle\CredentialBundle\Service\InitCredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'lle:credential:init',
    description: 'Initialize a project',
)]
class CredentialInitProjectCommand extends Command
{
    public function __construct(
        protected InitCredentialService $initCredentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initCredentialService->init();

        return Command::SUCCESS;
    }
}
