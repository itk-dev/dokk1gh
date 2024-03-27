<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Service\SmsServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sms:send',
    description: 'Send an SMS',
)]
class SmsSendCommand extends Command
{
    public function __construct(
        private readonly SmsServiceInterface $smsService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('recipient', InputArgument::REQUIRED, 'The recipient')
            ->addOption('message', null, InputOption::VALUE_REQUIRED, 'The message')
            ->addOption('flash', null, InputOption::VALUE_NONE, 'Send flash message (may not be supported by SMS gateway)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $recipient = $input->getArgument('recipient');
        $message = $input->getOption('message');

        if (!preg_match('/^(?<countryCode>45)?(?P<number>[0-9]{8})$/', $recipient, $matches)) {
            $io->error(sprintf('Invalid recipient: %s', $recipient));

            return Command::INVALID;
        }

        while (null === $message) {
            $message = $io->ask('Message');
        }

        $number = $matches['number'];
        $countryCode = $matches['countryCode'] ?? '';

        $options = [
            'flash' => (bool) $input->getOption('flash'),
        ];
        if ($this->smsService->send($number, $message, $countryCode, $options)) {
            $io->success(sprintf('SMS sent to %s%s', $countryCode, $number));

            return Command::SUCCESS;
        }
        $io->success(sprintf('Error sending SMS to %s%s', $countryCode, $number));

        return Command::FAILURE;
    }
}
