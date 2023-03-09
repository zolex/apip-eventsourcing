<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Functional;

use App\BookStore\Domain\Command\UpdateBookCommand;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookContent;
use App\BookStore\Domain\ValueObject\BookDescription;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\BookName;
use App\BookStore\Domain\ValueObject\Price;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Shared\Application\Command\CommandBusInterface;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UpdateBookTest extends KernelTestCase
{
    public function testUpdateBook(): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var BookRepositoryInterface $bookRepository */
        $bookRepository = static::getContainer()->get(BookRepositoryInterface::class);

        /** @var CommandBusInterface $commandBus */
        $commandBus = static::getContainer()->get(CommandBusInterface::class);

        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(id: $bookId)]);

        $commandBus->dispatch(new UpdateBookCommand(
            $bookId,
            name: new BookName('newName'),
            content: new BookContent('newContent'),
            price: new Price(2000),
        ));

        $book = $bookRepository->ofId($bookId);

        static::assertEquals(new BookName('newName'), $book->name());
        static::assertEquals(new BookDescription('description'), $book->description());
        static::assertEquals(new Author('author'), $book->author());
        static::assertEquals(new BookContent('newContent'), $book->content());
        static::assertEquals(new Price(2000), $book->price());
    }
}
