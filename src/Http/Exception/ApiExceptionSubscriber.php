<?php

declare(strict_types=1);

namespace App\Http\Exception;

use App\Shared\BusinessRuleViolation;
use App\Shared\NotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception')]
final class ApiExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof ValidationFailedException) {
            $violations = [];
            foreach ($e->getViolations() as $violation) {
                $violations[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => (string) $violation->getMessage(),
                    'code' => (string) $violation->getCode(),
                ];
            }
            $payload = [
                'code' => 'validation.failed',
                'message' => 'Validation failed',
                'violations' => $violations,
            ];
            $event->setResponse(new JsonResponse($payload, 422, ['Content-Type' => 'application/problem+json']));

            return;
        }

        if ($e instanceof NotFoundException) {
            $payload = [
                'code' => 'resource.not_found',
                'message' => 'Resource not found',
            ];
            $event->setResponse(new JsonResponse($payload, 404));

            return;
        }

        if ($e instanceof BusinessRuleViolation) {
            $payload = [
                'code' => $e->getRuleCode(),
                'message' => $e->getMessage(),
            ];
            $event->setResponse(new JsonResponse($payload, 409));

            return;
        }
    }
}
