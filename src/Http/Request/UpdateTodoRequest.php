<?php

declare(strict_types=1);

namespace App\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateTodoRequest
{
    public function __construct(
        #[Assert\Length(max: 200)]
        public readonly ?string $title = null,

        #[Assert\Choice(choices: ['open', 'done'])]
        public readonly ?string $status = null,
    ) {
    }
}
