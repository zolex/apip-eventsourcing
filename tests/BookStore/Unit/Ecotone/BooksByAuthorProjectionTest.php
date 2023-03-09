<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Unit\Ecotone;

use App\BookStore\Domain\Event\BookWasDeleted;
use App\BookStore\Domain\Event\BookWasUpdated;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Infrastructure\Ecotone\Projection\BooksByAuthorProjection;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;
use PHPUnit\Framework\TestCase;

final class BooksByAuthorProjectionTest extends TestCase
{
    public function testAddBookToAuthorBooksWithoutExistingAuthor(): void
    {
        $bookId = new BookId();

        $booksByAuthorState = $this->booksByAuthorProjection()->addBook(
            DummyBookFactory::createBookWasCreatedEvent(id: $bookId, author: 'authorOne'),
            [],
        );

        static::assertSame([
            'authorOne' => [(string) $bookId],
        ], $booksByAuthorState);
    }

    public function testAddBookToAuthorBooksWithExistingAuthor(): void
    {
        $bookId = new BookId();

        $booksByAuthorState = $this->booksByAuthorProjection()->addBook(
            DummyBookFactory::createBookWasCreatedEvent(id: $bookId, author: 'authorOne'),
            ['authorOne' => ['680d9111-fe46-49fd-8021-3d91c9fd3dca'], 'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76']],
        );

        static::assertSame([
            'authorOne' => ['680d9111-fe46-49fd-8021-3d91c9fd3dca', (string) $bookId],
            'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76'],
        ], $booksByAuthorState);
    }

    public function testAddBookToAuthorBooksWithExistingBook(): void
    {
        $bookId = new BookId();

        $booksByAuthorState = $this->booksByAuthorProjection()->addBook(
            DummyBookFactory::createBookWasCreatedEvent(id: $bookId, author: 'authorOne'),
            ['authorOne' => [(string) $bookId], 'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76']],
        );

        static::assertSame([
            'authorOne' => [(string) $bookId],
            'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76'],
        ], $booksByAuthorState);
    }

    public function testRemoveBookToAuthorBooksWithoutExistingAuthor(): void
    {
        $bookId = new BookId();

        $booksByAuthorState = $this->booksByAuthorProjection()->removeBook(
            new BookWasDeleted(id: $bookId),
            ['authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76']],
        );

        static::assertSame([
            'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76'],
        ], $booksByAuthorState);
    }

    public function testRemoveBookToAuthorBooksWithExistingAuthor(): void
    {
        $bookId = new BookId();

        $booksByAuthorState = $this->booksByAuthorProjection()->removeBook(
            new BookWasDeleted(id: $bookId),
            ['authorOne' => [(string) $bookId], 'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76']],
        );

        static::assertSame([
            'authorOne' => [],
            'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76'],
        ], $booksByAuthorState);
    }

    public function testUpdateBookToAuthorBooksWithoutExistingAuthor(): void
    {
        $bookId = new BookId();

        $booksByAuthorState = $this->booksByAuthorProjection()->updateBook(
            new BookWasUpdated(id: $bookId, author: new Author('authorThree')),
            ['authorOne' => ['680d9111-fe46-49fd-8021-3d91c9fd3dca'], 'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76']],
        );

        static::assertSame([
            'authorOne' => ['680d9111-fe46-49fd-8021-3d91c9fd3dca'],
            'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76'],
            'authorThree' => [(string) $bookId],
        ], $booksByAuthorState);
    }

    public function testUpdateBookToAuthorBooksWithExistingAuthor(): void
    {
        $bookId = new BookId();

        $booksByAuthorState = $this->booksByAuthorProjection()->updateBook(
            new BookWasUpdated(id: $bookId, author: new Author('authorThree')),
            ['authorOne' => [(string) $bookId, '680d9111-fe46-49fd-8021-3d91c9fd3dca'], 'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76']],
        );

        static::assertSame([
            'authorOne' => ['680d9111-fe46-49fd-8021-3d91c9fd3dca'],
            'authorTwo' => ['3cf6162d-8c05-4d80-9a39-ff1490f56c76'],
            'authorThree' => [(string) $bookId],
        ], $booksByAuthorState);
    }

    private function booksByAuthorProjection(): BooksByAuthorProjection
    {
        return new BooksByAuthorProjection();
    }
}
