<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GdprHelper
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly PropertyAccessorInterface $accessor,
        private readonly UserRepository $userRepository,
        private readonly array $options
    ) {
    }

    public function isGdprAccepted(UserInterface $user): bool
    {
        $property = $this->getGdprAcceptedProperty();

        return null !== $this->accessor->getValue($user, $property);
    }

    public function setGdprAccepted(UserInterface $user): void
    {
        $property = $this->getGdprAcceptedProperty();
        $value = $this->getGdprAcceptedAtValue();
        $this->accessor->setValue($user, $property, $value);
        $this->userRepository->persist($user, true);
    }

    public function getRedirectUrl(): string
    {
        $routeName = $this->options['accept_route'];
        $routeParameters = $this->options['accept_route_parameters'] ?? [];

        return $this->router->generate(
            $routeName,
            $routeParameters
        );
    }

    private function getGdprAcceptedProperty(): string
    {
        return $this->options['user_gdpr_property'];
    }

    private function getGdprAcceptedAtValue(): \DateTimeInterface
    {
        return new \DateTimeImmutable();
    }
}
