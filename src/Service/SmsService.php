<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class SmsService implements SmsServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly array $options
    ) {
    }

    public function send(string $number, string $message, string $countryCode)
    {
        try {
            $this->client->request(
                'POST',
                $this->options['sms_gateway_url'],
                [
                    'body' => [
                        'user' => $this->options['sms_gateway_username'],
                        'pass' => $this->options['sms_gateway_password'],
                        'countrycode' => $countryCode,
                        'number' => $number,
                        'message' => $message,
                        'charset' => 'UTF-8',
                    ],
                ]
            );

            return true;
        } catch (\Exception $exception) {
            // @TODO: Log exception.
        }

        return false;
    }
}
