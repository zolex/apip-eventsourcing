<?php

declare(strict_types=1);

namespace App\BookStore\Domain\ValueObject;

use Webmozart\Assert\Assert;

final readonly class BookContent
{
    public string $value;

    public function __construct(string $value)
    {
        Assert::lengthBetween($value, 1, 65535);

        $this->value = $value;
    }
}
