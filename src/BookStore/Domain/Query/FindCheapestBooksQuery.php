<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Query;

use App\Shared\Domain\Query\QueryInterface;

final readonly class FindCheapestBooksQuery implements QueryInterface
{
    public function __construct(public int $size = 10)
    {
    }
}
