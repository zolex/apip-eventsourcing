<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Event;

use App\BookStore\Domain\ValueObject\BookId;

final readonly class BookWasDeleted implements BookEvent
{
    public function __construct(
        private BookId $id,
    ) {
    }

    public function id(): BookId
    {
        return $this->id;
    }
}
