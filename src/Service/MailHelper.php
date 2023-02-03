<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Guest;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailHelper
{
    const MAIL_SENT = 'MAIL_SENT';

    /** @var MailerInterface */
    protected $mailer;

    /** @var EntityActionLogger */
    private $actionLogger;

    /** @var Configuration */
    private $configuration;

    /** @var TwigHelper */
    private $twigHelper;

    public function __construct(
        MailerInterface $mailer,
        EntityActionLogger $actionLogger,
        Configuration $configuration,
        TwigHelper $twigHelper
    ) {
        $this->mailer = $mailer;
        $this->actionLogger = $actionLogger;
        $this->twigHelper = $twigHelper;
        $this->configuration = $configuration;
    }

    public function sendApp(Guest $guest, $appUrl)
    {
        $from = new Address(
            $this->configuration->get('guest_app_email_sender_email'),
            $this->configuration->get('guest_app_email_sender_name')
        );
        $recipient = $guest->getEmail();
        $subject = $this->twigHelper->renderTemplate(
            $this->configuration->get('guest_app_email_subject_template'),
            [
                'guest' => $guest,
            ]
        );
        $bodyHtml = $this->twigHelper->renderTemplate(
            $this->configuration->get('guest_app_email_body_template'),
            [
                'guest' => $guest,
                'app_url' => $appUrl,
            ]
        );

        $template = $this->twigHelper->load('app/email/app.html.twig');
        $context = [
            'app_url' => $appUrl,
            'guest' => $guest,
            'subject' => $subject,
            'body_html' => $bodyHtml,
        ];
        $subject = $template->renderBlock('subject', $context);
        $bodyHtml = $template->renderBlock('body_html', $context);

        $message = (new Email())
            ->subject($subject)
            ->from($from)
            ->to($recipient)
            ->html($bodyHtml);

        $this->mailer->send($message);
        $this->actionLogger->log($guest, self::MAIL_SENT, [
            'message' => $bodyHtml,
            'guest' => $guest,
        ]);
    }
}
