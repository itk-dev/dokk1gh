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

class Sms2GoService implements SmsServiceInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly array $options
    ) {
    }

    /**
     * @see https://pushapi.ecmr.biz/docs/index.html?url=/swagger/v1/swagger.json#tag/SMS-gateway/operation/Sms_PostBatch
     */
    public function send(string $number, string $message, string $countryCode, array $options = [])
    {
        try {
            $this->client->request(
                'POST',
                $this->options['api_url'].'/'.$this->options['gateway_id'],
                [
                    'auth_bearer' => $this->options['api_key'],
                    'json' => [
                        'to' => [
                            $countryCode.$number,
                        ],
                        'body' => $message,
                        'flash' => (bool) ($options['flash'] ?? false),
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
