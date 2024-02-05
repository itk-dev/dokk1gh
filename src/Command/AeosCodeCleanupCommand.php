<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Entity\Code;
use App\Service\AeosHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:aeos:code-cleanup',
    description: 'Create user'
)]
class AeosCodeCleanupCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly AeosHelper $aeosHelper)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Don\'t do anything. Just show what will be done.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = $input->getOption('dry-run');
        $expiredCodes = $this->entityManager->getRepository(Code::class)->findExpired();
        if ($output->isVerbose()) {
            $output->writeln('#expired codes: '.\count($expiredCodes));
        }
        foreach ($expiredCodes as $code) {
            if ($dryRun) {
                $output->writeln('Delete code: '.$code);
            } else {
                if ($output->isVerbose()) {
                    $output->writeln('Deleting code: '.$code);
                }

                try {
                    $this->aeosHelper->deleteAeosIdentifier($code);
                    $this->entityManager->remove($code);
                    $this->entityManager->flush();
                    if ($output->isVerbose()) {
                        $output->writeln('Done');
                    }
                } catch (\Exception $ex) {
                    $output->writeln('Error deleting code: '.$code);
                    $output->writeln($ex->getMessage());
                    if ($output->isVerbose()) {
                        $output->writeln($ex->getTraceAsString());
                    }
                }
            }
        }

        return static::SUCCESS;
    }
}
