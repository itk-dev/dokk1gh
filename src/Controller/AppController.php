<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Guest;
use App\Entity\Template;
use App\Exception\AbstractException;
use App\Repository\CodeRepository;
use App\Service\Configuration;
use App\Service\GuestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AppController.
 */
#[Route(path: '/app/{guest}')]
class AppController extends AbstractController
{
    final public const GENERATED_CODE_SESSION_KEY = 'generated_code';

    public function __construct(
        private readonly GuestService $guestService,
        private readonly Configuration $configuration,
        private readonly RequestStack $requestStack
    ) {
    }

    #[Route(path: '/code', name: 'app_code', methods: ['GET'])]
    public function code(Guest $guest)
    {
        if (null !== $guest->getExpiredAt()) {
            return $this->render('app/expired.html.twig', ['guest' => $guest], new Response('', Response::HTTP_NOT_FOUND));
        }

        if (null === $guest->getActivatedAt()) {
            return $this->guide($guest);
        }

        $isValid = $this->guestService->isValid($guest);
        $canRequestCode = $this->guestService->canRequestCode($guest);

        return $this->render('app/code/index.html.twig', [
            'guest' => $guest,
            'guest_is_valid' => $isValid,
            'guest_can_request_code' => $canRequestCode,
        ]);
    }

    #[Route(path: '/card', name: 'app_card')]
    public function card(Guest $guest): Response
    {
        if (null !== $guest->getExpiredAt()) {
            return $this->render('app/expired.html.twig', ['guest' => $guest], new Response('', Response::HTTP_NOT_FOUND));
        }

        return $this->render('app/card/index.html.twig', [
            'guest' => $guest,
        ]);
    }

    #[Route(path: '/about', name: 'app_about')]
    public function about(Guest $guest): Response
    {
        return $this->render('app/about/index.html.twig', [
            'guest' => $guest,
            'replacements' => [
                'app://guide_url' => $this->generateUrl('app_guide', ['guest' => $guest->getId()]),
            ],
        ]);
    }

    #[Route(path: '/guide', name: 'app_guide', methods: ['GET'])]
    public function guide(Guest $guest): Response
    {
        return $this->render('app/onboard-guide/index.html.twig', [
            'guest' => $guest,
        ]);
    }

    #[Route(path: '/guide', name: 'app_accept', methods: ['POST'])]
    public function accept(Guest $guest): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->guestService->activate($guest);
        $this->addFlash('success', 'Guest accepted');

        return $this->redirectToRoute('app_code', [
            'guest' => $guest->getId(),
        ]);
    }

    #[Route(path: '/request/{template}', name: 'app_code_request', methods: ['POST'])]
    public function codeRequest(Guest $guest, Template $template)
    {
        if (!$guest->isEnabled() || null !== $guest->getExpiredAt()) {
            throw new AccessDeniedHttpException();
        }

        $code = null;
        $messages = null;
        $status = [];

        try {
            $code = $this->guestService->generateCode($guest, $template, 'Created by app');
            if (null !== $code) {
                $status['code_generated'] = true;
                $this->guestService->sendCode($guest, $code);
                $status['sms_sent'] = true;
            }
        } catch (AbstractException $exception) {
            return $this->render('app/code/error.html.twig', [
                'message' => $exception->getMessage(),
                'guest' => $guest,
            ]);
            $messages['danger'][] = $exception->getMessage();
        }
        $this->setGeneratedCodeData([$status, $messages]);

        return $this->redirectToRoute('app_code_request_result', [
            'code' => $code ? $code->getId() : null,
            'guest' => $guest->getId(),
            'template' => $template->getId(),
        ]);
    }

    #[Route(path: '/request/{template}', name: 'app_code_request_result', methods: ['GET'])]
    public function codeRequestResult(Request $request, Guest $guest, Template $template, CodeRepository $codeRepository): Response
    {
        [$status, $messages] = $this->getGeneratedCodeData();
        $codeId = $request->get('code');
        $code = null !== $codeId
            ? $codeRepository->find($codeId)
            : null;

        return $this->render('app/code/request_result.html.twig', [
            'guest' => $guest,
            'template' => $template,
            'code' => $code,
            'messages' => $messages,
            'status' => $status,
        ]);
    }

    #[Route(path: '/manifest.json', name: 'app_manifest')]
    public function manifest(Guest $guest, Packages $packages)
    {
        $manifest = [
            'short_name' => $this->configuration->get('pwa_app_short_name'),
            'name' => $this->configuration->get('pwa_app_name'),
            'icons' => array_map(function ($width) use ($packages) {
                $sizes = $width.'x'.$width;

                return [
                    'src' => $packages->getUrl($this->configuration->get('app_icons.'.$sizes)),
                    'sizes' => $sizes,
                    'type' => 'image/png',
                    'maskable' => true,
                ];
            }, [20, 29, 40, 48, 58, 60, 72, 76, 80, 87, 96, 120, 144, 152, 167, 180, 192, 512, 1024]),
            'start_url' => $this->generateUrl('app_code', [
                'guest' => $guest->getId(),
                'utm_source' => 'homescreen',
            ]),
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => $this->configuration->get('app_background_color', '#003764'),
            'theme_color' => $this->configuration->get('app_theme_color', '#003764'),
            'lang' => $this->configuration->get('app_lang', 'da'),
        ];

        return new JsonResponse($manifest);
    }

    #[Route(path: '/offline', name: 'app_offline')]
    public function offline(): Response
    {
        return $this->render('app/offline.html.twig');
    }

    #[Route(path: '/no-access', name: 'app_no_access')]
    public function noAccess(): Response
    {
        return $this->render('app/no-access.html.twig');
    }

    #[Route(path: '/serviceworker.js', name: 'app_serviceworker')]
    public function serviceworker()
    {
        $content = $this->renderView('app/javascripts/sw.js.twig');

        return new Response($content, Response::HTTP_OK, ['content-type' => 'text/javascript']);
    }

    private function setGeneratedCodeData($data)
    {
        $this->requestStack->getSession()->set(self::GENERATED_CODE_SESSION_KEY, $data);
    }

    private function getGeneratedCodeData($peek = false)
    {
        $session = $this->requestStack->getSession();
        $data = null;
        if ($session->has(self::GENERATED_CODE_SESSION_KEY)) {
            $data = $session->get(self::GENERATED_CODE_SESSION_KEY);
            if (!$peek) {
                // $session->remove(self::GENERATED_CODE_SESSION_KEY);
            }
        }

        return $data;
    }
}
