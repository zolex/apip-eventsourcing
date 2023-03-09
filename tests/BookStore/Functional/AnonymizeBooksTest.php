<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Functional;

use App\BookStore\Domain\Command\AnonymizeBooksCommand;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Shared\Application\Command\CommandBusInterface;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AnonymizeBooksTest extends KernelTestCase
{
    public function testAnonymizeAuthorOfBooks(): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var CommandBusInterface $commandBus */
        $commandBus = static::getContainer()->get(CommandBusInterface::class);

        for ($i = 0; $i < 10; ++$i) {
            $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
                id: $bookId,
                author: sprintf('author_%d', $i),
            )]);
        }

        $commandBus->dispatch(new AnonymizeBooksCommand('anon.'));

        foreach (static::getContainer()->get(BookRepositoryInterface::class)->all() as $book) {
            self::assertEquals(new Author('anon.'), $book->author());
        }
    }
}
