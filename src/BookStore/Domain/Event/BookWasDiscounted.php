<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Event;

use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\Discount;

final readonly class BookWasDiscounted implements BookEvent
{
    public function __construct(
        private BookId $id,
        public Discount $discount,
    ) {
    }

    public function id(): BookId
    {
        return $this->id;
    }
}
