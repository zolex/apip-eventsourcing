<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Projection;

use App\BookStore\Domain\Event\BookWasCreated;
use App\BookStore\Domain\Event\BookWasDeleted;
use App\BookStore\Domain\Event\BookWasUpdated;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookId;
use Ecotone\EventSourcing\Attribute\Projection;
use Ecotone\EventSourcing\Attribute\ProjectionState;
use Ecotone\Modelling\Attribute\EventHandler;

#[Projection(self::NAME, Book::class)]
final class BooksByAuthorProjection
{
    public const NAME = 'booksByAuthor';

    /**
     * @param array<string, list<string>> $booksByAuthorState
     *
     * @return array<string, list<string>>
     */
    #[EventHandler]
    public function addBook(BookWasCreated $event, #[ProjectionState] array $booksByAuthorState): array
    {
        return $this->addBookToAuthorBooks($event->id(), $event->author, $booksByAuthorState);
    }

    /**
     * @param array<string, list<string>> $booksByAuthorState
     *
     * @return array<string, list<string>>
     */
    #[EventHandler]
    public function updateBook(BookWasUpdated $event, #[ProjectionState] array $booksByAuthorState): array
    {
        if (!$event->author) {
            return $booksByAuthorState;
        }

        $booksByAuthorState = $this->removeBookFromAuthorBooks($event->id(), $booksByAuthorState);

        return $this->addBookToAuthorBooks($event->id(), $event->author, $booksByAuthorState);
    }

    /**
     * @param array<string, list<string>> $booksByAuthorState
     *
     * @return array<string, list<string>>
     */
    #[EventHandler]
    public function removeBook(BookWasDeleted $event, #[ProjectionState] array $booksByAuthorState): array
    {
        return $this->removeBookFromAuthorBooks($event->id(), $booksByAuthorState);
    }

    /**
     * @param array<string, list<string>> $booksByAuthorState
     *
     * @return array<string, list<string>>
     */
    private function addBookToAuthorBooks(BookId $bookId, Author $author, array $booksByAuthorState): array
    {
        if (!isset($booksByAuthorState[$author->value])) {
            $booksByAuthorState[$author->value] = [];
        }

        if ($this->findBookAuthor($bookId, $booksByAuthorState)?->isEqualTo($author)) {
            return $booksByAuthorState;
        }

        $booksByAuthorState[$author->value][] = (string) $bookId;

        return $booksByAuthorState;
    }

    /**
     * @param array<string, list<string>> $booksByAuthorState
     *
     * @return array<string, list<string>>
     */
    private function removeBookFromAuthorBooks(BookId $bookId, array $booksByAuthorState): array
    {
        $previousAuthor = $this->findBookAuthor($bookId, $booksByAuthorState);

        if (!$previousAuthor) {
            return $booksByAuthorState;
        }

        $previousBookIndex = array_search((string) $bookId, $booksByAuthorState[$previousAuthor->value], true);
        unset($booksByAuthorState[$previousAuthor->value][$previousBookIndex]);
        $booksByAuthorState[$previousAuthor->value] = array_values($booksByAuthorState[$previousAuthor->value]);

        return $booksByAuthorState;
    }

    /**
     * @param array<string, list<string>> $booksByAuthorState
     */
    private function findBookAuthor(BookId $bookId, array $booksByAuthorState): ?Author
    {
        foreach ($booksByAuthorState as $author => $books) {
            if (in_array((string) $bookId, $books, true)) {
                return new Author($author);
            }
        }

        return null;
    }
}
