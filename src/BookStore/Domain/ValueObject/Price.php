<?php

declare(strict_types=1);

namespace App\BookStore\Domain\ValueObject;

use Webmozart\Assert\Assert;

final readonly class Price
{
    public int $amount;

    public function __construct(int $amount)
    {
        Assert::greaterThanEq($amount, 0);

        $this->amount = $amount;
    }

    public function applyDiscount(Discount $discount): static
    {
        $amount = (int) ($this->amount - ($this->amount * $discount->percentage / 100));

        return new static($amount);
    }
}
