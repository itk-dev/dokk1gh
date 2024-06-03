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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mock/sms2go')]
class Sms2GoGatewayController extends AbstractController
{
    public function __construct(
        private readonly ActionLogManager $manager,
        private readonly array $options
    ) {
    }

    #[Route(path: '/{gatewayId}', methods: [Request::METHOD_POST])]
    public function send(Request $request, string $gatewayId): Response
    {
        if ($gatewayId !== $this->options['gateway_id']) {
            return new JsonResponse([
                'message' => 'APIKey validation failed',
                'exceptionType' => 'SimpleHttpResponseException',
                'actionName' => 'PostBatch',
                'controllerName' => 'Sms',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!preg_match('/^Bearer\s+(?P<key>.+)$/i', $request->headers->get('authorization'), $matches)
            || $matches['key'] !== $this->options['api_key']) {
            return new JsonResponse([
                'message' => 'Authorization has been denied for this request.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $payload = $request->toArray();
        } catch (JsonException $jsonException) {
            return new JsonResponse([
                'message' => 'The request is invalid.',
                'modelState' => ['model.body' => ['An error has occurred.']],
                'exceptionType' => 'InvalidOperationException',
                'actionName' => 'PostBatch',
                'controllerName' => 'Sms',
            ], Response::HTTP_BAD_REQUEST);
        }

        $message = $payload['body'] ?? null;
        $recipients = $payload['to'] ?? null;
        if (!isset($message, $recipients) || !\is_array($recipients)) {
            return new JsonResponse([
                'message' => 'The request is invalid.',
                'modelState' => ['model' => ['An error has occurred.']],
                'exceptionType' => 'InvalidOperationException',
                'actionName' => 'PostBatch',
                'controllerName' => 'Sms',
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($recipients as $recipient) {
            $number = $recipient;
            $this->manager->log(new SmsGatewayActionLogEntry('send_sms', [
                'number' => $number,
                'message' => $message,
            ]));
        }

        return new Response((string) random_int(100_000_000, 999_999_999));
    }
}
