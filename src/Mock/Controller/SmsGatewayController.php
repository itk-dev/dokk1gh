<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Controller;

use App\Mock\Entity\SmsGatewayActionLogEntry;
use App\Mock\Service\ActionLogManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mock/sms')]
class SmsGatewayController extends AbstractController
{
    public function __construct(
        private readonly ActionLogManager $manager
    ) {
    }

    #[Route]
    public function index(): RedirectResponse
    {
        return $this->redirectToRoute('sms_log');
    }

    #[Route(path: '/log', name: 'sms_log')]
    public function log(ManagerRegistry $registry): Response
    {
        $entries = $this->manager->findAll(SmsGatewayActionLogEntry::class);

        return $this->render('@Mock/smsgateway/index.html.twig', [
            'entries' => $entries,
        ]);
    }

    #[Route(path: '/log/latest', name: 'sms_log_latest')]
    public function logLatest(): Response
    {
        $entry = $this->manager->findOne(SmsGatewayActionLogEntry::class);

        return $this->show($entry);
    }

    #[Route(path: '/log/{id}', name: 'sms_log_show')]
    public function show(SmsGatewayActionLogEntry $entry): Response
    {
        $prev = null;
        $next = null;
        $entries = $this->manager->findAll(SmsGatewayActionLogEntry::class);
        foreach ($entries as $index => $e) {
            if ($e === $entry) {
                if ($index > 0) {
                    $prev = $entries[$index - 1];
                }
                if ($index < \count($entries) - 1) {
                    $next = $entries[$index + 1];
                }

                break;
            }
        }

        return $this->render('@Mock/smsgateway/show.html.twig', [
            'entry' => $entry,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    #[Route(path: '/send', methods: ['GET', 'POST'])]
    public function send(Request $request): Response
    {
        $username = $request->get('user');
        $password = $request->get('pass');
        $countryCode = $request->get('countrycode');
        $number = $request->get('number');
        $message = $request->get('message');
        $callbackUrl = $request->get('callbackurl');

        $messageId = -1;
        $status = -1;
        $statusDescription = 'error';

        if (empty($username) || $username !== $password) {
            $status = 87;
            $statusDescription = 'Invalid credentials';
        } else {
            if ($countryCode && $number && $message) {
                $messageId = uniqid();
                $status = 0;
                $statusDescription = 'ok';
            } else {
                $status = 42;
                $statusDescription = 'Missing country, number or message';
            }
        }

        $this->manager->log(new SmsGatewayActionLogEntry('send_sms', [
            'countrycode' => $countryCode,
            'number' => $number,
            'message' => $message,
            'msg_id' => $messageId,
            'status' => $status,
            'status_description' => $statusDescription,
        ]));

        if (null !== $callbackUrl) {
            $url = $callbackUrl
                .(!str_contains((string) $callbackUrl, '?') ? '?' : ':')
                .http_build_query([
                    'msg_id' => $messageId,
                    'status' => $status,
                    'status_description' => $statusDescription,
                ]);
            $ch = curl_init($url);
            curl_exec($ch);
            curl_close($ch);
        }

        return new Response((string) $status);
    }

    #[Route(path: '/send/callback', methods: ['GET'])]
    public function sendCallback(Request $request): Response
    {
        $messageId = $request->get('msg_id');
        $status = $request->get('status');
        $statusDescription = $request->get('status_description');

        $this->manager->log(new SmsGatewayActionLogEntry('sent_sms', [
            'msg_id' => $messageId,
            'status' => $status,
            'status_description' => $statusDescription,
        ]));

        return new Response();
    }
}
