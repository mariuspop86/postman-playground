<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class ErrorController extends AbstractController
{
    #[Route('/error', name: 'error')]
    public function error(\Throwable $exception): JsonResponse
    {
        return new JsonResponse(['message' => 'Well this is embarrassing'], Response::HTTP_BAD_REQUEST);
    }
}
