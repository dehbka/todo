<?php

declare(strict_types=1);

namespace App\Shared;

final class BusinessRuleViolation extends \RuntimeException
{
    private string $ruleCode;

    public function __construct(string $code, string $message)
    {
        parent::__construct($message);
        $this->ruleCode = $code;
    }

    public function getRuleCode(): string
    {
        return $this->ruleCode;
    }

    public static function conflict(string $code, string $message): self
    {
        return new self($code, $message);
    }
}
