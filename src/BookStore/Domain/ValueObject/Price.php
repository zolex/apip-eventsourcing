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
        $amount = (int) ((float)$this->amount - ((float)$this->amount * (float)$discount->percentage / 100.0));

        return new static($amount);
    }
}
