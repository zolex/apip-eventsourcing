<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Event;

use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookContent;
use App\BookStore\Domain\ValueObject\BookDescription;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\BookName;
use App\BookStore\Domain\ValueObject\Price;

final readonly class BookWasCreated implements BookEvent
{
    public function __construct(
        private BookId $id,
        public BookName $name,
        public BookDescription $description,
        public Author $author,
        public BookContent $content,
        public Price $price,
    ) {
    }

    public function id(): BookId
    {
        return $this->id;
    }
}
