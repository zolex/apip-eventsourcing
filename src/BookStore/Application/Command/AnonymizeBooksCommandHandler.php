<?php

declare(strict_types=1);

namespace App\BookStore\Application\Command;

use App\BookStore\Domain\Command\AnonymizeBooksCommand;
use App\BookStore\Domain\Command\UpdateBookCommand;
use App\BookStore\Domain\Repository\BookRepositoryInterface;
use App\BookStore\Domain\ValueObject\Author;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Command\CommandHandlerInterface;
use Ecotone\Modelling\Attribute\CommandHandler;

final readonly class AnonymizeBooksCommandHandler implements CommandHandlerInterface
{
    public function __construct(private BookRepositoryInterface $bookRepository, private CommandBusInterface $commandBus)
    {
    }

    #[CommandHandler]
    public function __invoke(AnonymizeBooksCommand $command): void
    {
        $books = $this->bookRepository->all();

        foreach ($books as $book) {
            $this->commandBus->dispatch(new UpdateBookCommand(
                id: $book->id(),
                author: new Author($command->anonymizedName),
            ));
        }
    }
}
