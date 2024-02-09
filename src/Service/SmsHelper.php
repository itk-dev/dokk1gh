<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Code;
use App\Entity\Guest;

class SmsHelper
{
    final public const SMS_SENT = 'SMS_SENT';

    /** @var SmsServiceInterface */
    protected $smsService;

    /** @var \Twig_Environment */
    private $twigHelper;

    public function __construct(
        SmsServiceInterface $smsService,
        private readonly EntityActionLogger $actionLogger,
        private readonly Configuration $configuration,
        TwigHelper $twigHelper
    ) {
        $this->smsService = $smsService;
        $this->twigHelper = $twigHelper;
    }

    public function sendApp(Guest $guest, $appUrl)
    {
        $number = $guest->getPhone();
        $countryCode = $guest->getPhoneCountryCode();
        $message = $this->twigHelper->renderTemplate(
            $this->configuration->get('guest_app_sms_body_template'),
            [
                'guest' => $guest,
                'app_url' => $appUrl,
            ]
        );
        $this->smsService->send($number, $message, $countryCode);

        $this->actionLogger->log($guest, self::SMS_SENT, [
            'guest' => $guest,
            'message' => $message,
        ]);
    }

    public function sendCode(Guest $guest, Code $code)
    {
        $number = $guest->getPhone();
        $countryCode = $guest->getPhoneCountryCode();

        $codeValidTimePeriod = $this->twigHelper->renderTemplate(
            $this->configuration->get('code_valid_time_period'),
            [
                'code' => $code,
            ]
        );

        $message = $this->twigHelper->renderTemplate(
            $this->configuration->get('guest_code_sms_template'),
            [
                'guest' => $guest,
                'code' => $code,
                'code_valid_time_period' => $codeValidTimePeriod,
            ]
        );
        $this->smsService->send($number, $message, $countryCode);

        $this->actionLogger->log($guest, self::SMS_SENT, [
            'guest' => $guest,
            'code' => $code,
            'message' => $message,
        ]);
    }
}
