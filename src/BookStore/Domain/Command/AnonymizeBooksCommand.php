<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Command;

use App\Shared\Domain\Command\CommandInterface;

final readonly class AnonymizeBooksCommand implements CommandInterface
{
    public function __construct(
        public string $anonymizedName,
    ) {
    }
}
