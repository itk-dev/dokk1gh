<?php

namespace App\Command;

use App\Service\AeosService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:aeos:test',
)]
class AeosTestCommand extends Command
{
    public function __construct(
        private readonly AeosService $aeosService,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->aeosService->getTemplates();
        $output->writeln(json_encode($result));

        return static::SUCCESS;
    }
}
