<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Symfony\Component\Uid\Uuid;

trait AggregateRootId
{
    public readonly string $value;

    final public function __construct(?string $value = null)
    {
        $this->value = $value ?? (string) Uuid::v4();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
