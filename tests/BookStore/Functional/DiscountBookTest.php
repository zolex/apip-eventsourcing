<?php

declare(strict_types=1);

namespace App\Tests\BookStore\Functional;

use App\BookStore\Domain\Command\DiscountBookCommand;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\Discount;
use App\BookStore\Domain\ValueObject\Price;
use App\BookStore\Infrastructure\Ecotone\Repository\EventSourcedBookRepository;
use App\Shared\Application\Command\CommandBusInterface;
use App\Tests\BookStore\DummyFactory\DummyBookFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DiscountBookTest extends KernelTestCase
{
    /**
     * @dataProvider applyADiscountOnBookDataProvider
     */
    public function testApplyADiscountOnBook(int $initialAmount, int $discount, int $expectedAmount): void
    {
        /** @var EventSourcedBookRepository $eventSourcedBookRepository */
        $eventSourcedBookRepository = static::getContainer()->get(EventSourcedBookRepository::class);

        /** @var BookRepositoryInterface $bookRepository */
        $bookRepository = static::getContainer()->get(BookRepositoryInterface::class);

        /** @var CommandBusInterface $commandBus */
        $commandBus = static::getContainer()->get(CommandBusInterface::class);

        $eventSourcedBookRepository->save($bookId = new BookId(), 0, [DummyBookFactory::createBookWasCreatedEvent(
            id: $bookId,
            price: $initialAmount,
        )]);

        $commandBus->dispatch(new DiscountBookCommand($bookId, new Discount($discount)));

        static::assertEquals(new Price($expectedAmount), $bookRepository->ofId($bookId)->price());
    }

    public function applyADiscountOnBookDataProvider(): iterable
    {
        yield [100, 0, 100];
        yield [100, 20, 80];
        yield [50, 30, 35];
        yield [50, 100, 0];
    }
}
