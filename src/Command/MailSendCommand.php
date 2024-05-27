<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Service\MailHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:mail:send',
    description: 'Send an email',
)]
class MailSendCommand extends Command
{
    public function __construct(
        private readonly MailHelper $mailHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('subject', InputArgument::OPTIONAL, 'The subject')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'The sender')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'The recipient')
            ->addOption('html', null, InputOption::VALUE_REQUIRED, 'The HTML message')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $subject = $input->getArgument('subject') ?? 'Test email';
        $from = $input->getOption('from');
        while (!filter_var($from, \FILTER_VALIDATE_EMAIL)) {
            $from = $io->ask('From');
        }
        $to = $input->getOption('to');
        while (!filter_var($to, \FILTER_VALIDATE_EMAIL)) {
            $to = $io->ask('To');
        }
        $html = $input->getOption('html') ?? '<p>A <em>test</em> email</p>';

        $this->mailHelper->sendEmail(
            $subject,
            $from,
            $to,
            $html
        );

        return Command::SUCCESS;
    }
}
