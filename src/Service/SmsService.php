<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use GuzzleHttp\Client;

class SmsService implements SmsServiceInterface
{
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function send($number, $message, $countryCode)
    {
        try {
            $client = new Client();
            $res = $client->request(
                'POST',
                $this->configuration->get('sms_gateway_url'),
                [
                    'form_params' => [
                        'user' => $this->configuration->get('sms_gateway_username'),
                        'pass' => $this->configuration->get('sms_gateway_password'),
                        'countrycode' => $countryCode,
                        'number' => $number,
                        'message' => $message,
                        'charset' => 'UTF-8',
                    ],
                ]
            );

            return true;
        } catch (\Exception $ex) {
            throw $ex;
            // @TODO: Log exception.
        }

        return false;
    }
}
