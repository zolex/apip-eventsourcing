<?php

declare(strict_types=1);

namespace App\BookStore\Domain\ValueObject;

use Webmozart\Assert\Assert;

final readonly class BookDescription
{
    public string $value;

    public function __construct(string $value)
    {
        Assert::lengthBetween($value, 1, 1023);

        $this->value = $value;
    }
}
