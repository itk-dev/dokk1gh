<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Repository\SettingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:settings:list',
    description: 'List all settings',
)]
class SettingsListCommand extends Command
{
    public function __construct(
        private readonly SettingRepository $settings
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $settings = $this->settings->findAll();
        foreach ($settings as $setting) {
            $io->definitionList(
                ['category' => ''],
                ['name' => 'type']
            );
        }

        return Command::SUCCESS;
    }
}
