<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Projection;

use App\BookStore\Domain\Event\BookWasCreated;
use App\BookStore\Domain\Event\BookWasDeleted;
use App\BookStore\Domain\Model\Book;
use Ecotone\EventSourcing\Attribute\Projection;
use Ecotone\EventSourcing\Attribute\ProjectionState;
use Ecotone\Modelling\Attribute\EventHandler;

#[Projection(self::NAME, Book::class)]
final class BookIdsProjection
{
    public const NAME = 'bookList';

    /**
     * @param list<string> $bookIdsState
     *
     * @return list<string>
     */
    #[EventHandler]
    public function addBook(BookWasCreated $event, #[ProjectionState] array $bookIdsState): array
    {
        $bookIdsState[] = (string) $event->id();

        return $bookIdsState;
    }

    /**
     * @param array<string> $bookIdsState
     *
     * @return list<string>
     */
    #[EventHandler]
    public function removeBook(BookWasDeleted $event, #[ProjectionState] array $bookIdsState): array
    {
        if (false !== $index = array_search((string) $event->id(), $bookIdsState, true)) {
            unset($bookIdsState[$index]);
        }

        return array_values($bookIdsState);
    }
}
