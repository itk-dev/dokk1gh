<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Service\GdprHelper;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[Route('/gdpr', name: 'gdpr_')]
class GdprController extends AbstractDashboardController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly GdprHelper $helper
    ) {
    }

    #[Route(path: '/show', name: 'show', methods: [Request::METHOD_GET])]
    public function show(Request $request): Response
    {
        $form = $this->createGdprForm($request->get('referrer'));

        return $this->render('gdpr/show.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/accept', name: 'accept', methods: [Request::METHOD_POST])]
    public function accept(Request $request): Response
    {
        $form = $this->createGdprForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && true === $form->get('accept')->getData()) {
            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                $user = $token->getUser();
                $this->helper->setGdprAccepted($user);

                $referrer = $form->get('referrer')->getData();

                return $this->redirect($referrer ?: '/');
            }
        }

        $this->addFlash('danger', new TranslatableMessage('You must accept GDPR to continue'));

        return $this->redirectToRoute('gdpr_show');
    }

    private function createGdprForm(?string $referrer = null): FormInterface
    {
        return $this->createFormBuilder(['referrer' => $referrer])
            ->setAction($this->generateUrl('gdpr_accept'))
            ->setMethod('POST')
            ->add('accept', CheckboxType::class, [
                'required' => true,
                'label' => new TranslatableMessage('Accept GDPR'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => new TranslatableMessage('Accept'),
            ])
          ->add('referrer', HiddenType::class)
            ->getForm();
    }
}
