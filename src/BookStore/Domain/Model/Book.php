<?php

declare(strict_types=1);

namespace App\BookStore\Domain\Model;

use App\BookStore\Domain\Command\CreateBookCommand;
use App\BookStore\Domain\Command\DeleteBookCommand;
use App\BookStore\Domain\Command\DiscountBookCommand;
use App\BookStore\Domain\Command\UpdateBookCommand;
use App\BookStore\Domain\Event\BookWasCreated;
use App\BookStore\Domain\Event\BookWasDeleted;
use App\BookStore\Domain\Event\BookWasDiscounted;
use App\BookStore\Domain\Event\BookWasUpdated;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookContent;
use App\BookStore\Domain\ValueObject\BookDescription;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\BookName;
use App\BookStore\Domain\ValueObject\Price;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\EventSourcingAggregate;
use Ecotone\Modelling\Attribute\EventSourcingHandler;
use Ecotone\Modelling\Attribute\Identifier;
use Ecotone\Modelling\WithAggregateVersioning;

/**
 * @psalm-suppress MissingConstructor
 */
#[EventSourcingAggregate]
final class Book
{
    use WithAggregateVersioning;

    #[Identifier]
    private BookId $id;
    private BookName $name;
    private BookDescription $description;
    private Author $author;
    private BookContent $content;
    private Price $price;
    private bool $deleted = false;

    #[CommandHandler]
    public static function create(CreateBookCommand $command): array
    {
        return [
            new BookWasCreated(
                id: new BookId(),
                name: $command->name,
                description: $command->description,
                author: $command->author,
                content: $command->content,
                price: $command->price,
            ),
        ];
    }

    #[CommandHandler]
    public function update(UpdateBookCommand $command): array
    {
        return [
            new BookWasUpdated(
                id: $command->id,
                name: $command->name,
                description: $command->description,
                author: $command->author,
                content: $command->content,
                price: $command->price,
            ),
        ];
    }

    #[CommandHandler]
    public function delete(DeleteBookCommand $command): array
    {
        return [new BookWasDeleted($command->id)];
    }

    #[CommandHandler]
    public function discount(DiscountBookCommand $command): array
    {
        return [new BookWasDiscounted(
            id: $command->id,
            discount: $command->discount,
        )];
    }

    #[EventSourcingHandler]
    public function applyBookWasCreated(BookWasCreated $event): void
    {
        $this->id = $event->id();
        $this->name = $event->name;
        $this->description = $event->description;
        $this->author = $event->author;
        $this->content = $event->content;
        $this->price = $event->price;
    }

    #[EventSourcingHandler]
    public function applyBookWasUpdated(BookWasUpdated $event): void
    {
        $this->name = $event->name ?? $this->name;
        $this->description = $event->description ?? $this->description;
        $this->author = $event->author ?? $this->author;
        $this->content = $event->content ?? $this->content;
        $this->price = $event->price ?? $this->price;
    }

    #[EventSourcingHandler]
    public function applyBookWasDeleted(BookWasDeleted $event): void
    {
        $this->deleted = true;
    }

    #[EventSourcingHandler]
    public function applyBookWasDiscounted(BookWasDiscounted $event): void
    {
        $this->price = $this->price->applyDiscount($event->discount);
    }

    public function id(): BookId
    {
        return $this->id;
    }

    public function name(): BookName
    {
        return $this->name;
    }

    public function description(): BookDescription
    {
        return $this->description;
    }

    public function author(): Author
    {
        return $this->author;
    }

    public function content(): BookContent
    {
        return $this->content;
    }

    public function price(): Price
    {
        return $this->price;
    }

    public function deleted(): bool
    {
        return $this->deleted;
    }
}
