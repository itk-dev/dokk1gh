<?php

namespace App\Service;

interface SmsServiceInterface
{
    /**
     * Send an SMS message to the specified recipient (mobile phone number).
     *
     * @return bool
     */
    public function send(string $number, string $message, string $countryCode, array $options = []);
}
