<?php

declare(strict_types=1);

namespace App\Shared;

final class NotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Resource not found')
    {
        parent::__construct($message);
    }

    public static function resource(string $message = 'Resource not found'): self
    {
        return new self($message);
    }
}
