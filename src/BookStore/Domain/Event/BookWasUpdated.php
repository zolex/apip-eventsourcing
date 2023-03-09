<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Event;

use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookContent;
use App\BookStore\Domain\ValueObject\BookDescription;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\BookName;
use App\BookStore\Domain\ValueObject\Price;

final readonly class BookWasUpdated implements BookEvent
{
    public function __construct(
        private BookId $id,
        public ?BookName $name = null,
        public ?BookDescription $description = null,
        public ?Author $author = null,
        public ?BookContent $content = null,
        public ?Price $price = null,
    ) {
    }

    public function id(): BookId
    {
        return $this->id;
    }
}
