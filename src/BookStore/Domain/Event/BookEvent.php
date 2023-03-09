<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Event;

use App\BookStore\Domain\ValueObject\BookId;

interface BookEvent
{
    public function id(): BookId;
}
