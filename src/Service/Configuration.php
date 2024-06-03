<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Repository\SettingRepository;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Configuration
{
    public function __construct(
        private readonly SettingRepository $settingRepository,
        private readonly ParameterBagInterface $parameters
    ) {
    }

    public function get(string $path, mixed $defaultValue = null): mixed
    {
        $settings = $this->settingRepository->all();
        if (isset($settings[$path])) {
            return $settings[$path];
        }

        if ($this->parameters->has($path)) {
            $config = $this->parameters->get($path);
        } elseif (str_contains((string) $path, '.')) {
            $steps = explode('.', (string) $path);
            foreach ($steps as $index => $step) {
                if (0 === $index) {
                    $config = $this->parameters->get($step);
                } elseif (!isset($config[$step])) {
                    throw new ParameterNotFoundException(implode('.', \array_slice($steps, 0, $index + 1)));
                } else {
                    $config = $config[$step];
                }
            }
        }

        if ('code.config' === $path) {
            $config['daysDisabled'] = array_values(
                array_diff(
                    range(0, 6),
                    $config['daysEnabled'] ?? []
                )
            );
        }

        return $config ?? $defaultValue;
    }
}
