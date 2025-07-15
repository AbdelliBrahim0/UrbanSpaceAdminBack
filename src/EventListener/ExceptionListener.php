<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse([
                'error' => 'Ressource non trouvée',
                'message' => $exception->getMessage()
            ], Response::HTTP_NOT_FOUND);
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'error' => 'Erreur HTTP',
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        } else {
            $response = new JsonResponse([
                'error' => 'Erreur serveur',
                'message' => 'Une erreur interne est survenue'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $event->setResponse($response);
    }
}