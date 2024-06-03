<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Api;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class AbstractApiController extends AbstractController
{
    protected function createResponse(mixed $data, int $status = Response::HTTP_OK): JsonResponse
    {
        $data ??= [];
        $data = (array) $data;
        if (array_is_list($data)) {
            $data = array_map(
                static fn ($item) => (array) $item,
                $data
            );
        }

        return new JsonResponse($data, $status);
    }

    protected function createHttpExceptionResponse(HttpExceptionInterface $exception): JsonResponse
    {
        return new JsonResponse([
            'status' => 'error',
            'message' => $exception->getMessage(),
        ], $exception->getStatusCode());
    }
}
