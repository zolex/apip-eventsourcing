<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Command;

use App\BookStore\Domain\ValueObject\BookId;
use App\Shared\Domain\Command\CommandInterface;

final readonly class DeleteBookCommand implements CommandInterface
{
    public function __construct(
        public BookId $id,
    ) {
    }
}
