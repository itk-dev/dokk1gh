<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

interface SmsServiceInterface
{
    /**
     * Send an SMS message to the specified recipient (mobile phone number).
     *
     * @param $number
     * @param $message
     * @param $countryCode
     *
     * @return bool
     */
    public function send($number, $message, $countryCode);
}
