<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

interface SmsServiceInterface
{
    /**
     * Send an SMS message to the specified recipient (mobile phone number).
     *
     * @return bool
     */
    public function send(string $number, string $message, string $countryCode);
}
