<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Code;
use App\Entity\Guest;

class SmsHelper
{
    const SMS_SENT = 'SMS_SENT';

    /** @var \App\Service\SmsServiceInterface */
    protected $smsService;

    /** @var EntityActionLogger */
    private $actionLogger;

    /** @var Configuration */
    private $configuration;

    /** @var \Twig_Environment */
    private $twigHelper;

    public function __construct(
        SmsServiceInterface $smsService,
        EntityActionLogger $actionLogger,
        Configuration $configuration,
        TwigHelper $twigHelper
    ) {
        $this->smsService = $smsService;
        $this->actionLogger = $actionLogger;
        $this->twigHelper = $twigHelper;
        $this->configuration = $configuration;
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
                'guest_name' => $guest->getName(),
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
