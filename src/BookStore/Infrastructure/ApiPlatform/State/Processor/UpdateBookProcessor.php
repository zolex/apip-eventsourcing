<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BookStore\Domain\Command\UpdateBookCommand;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\Query\FindBookQuery;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Domain\ValueObject\BookContent;
use App\BookStore\Domain\ValueObject\BookDescription;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\BookName;
use App\BookStore\Domain\ValueObject\Price;
use App\BookStore\Infrastructure\ApiPlatform\Resource\BookResource;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Query\QueryBusInterface;
use Webmozart\Assert\Assert;

final readonly class UpdateBookProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): BookResource
    {
        Assert::isInstanceOf($data, BookResource::class);
        $previous = $context['previous_data'] ?? new \stdClass;
        Assert::isInstanceOf($previous, BookResource::class);

        $id = (string) $previous->id;

        $command = new UpdateBookCommand(
            new BookId($id),
            null !== $data->name && $previous->name !== $data->name ? new BookName($data->name) : null,
            null !== $data->description && $previous->description !== $data->description ? new BookDescription($data->description) : null,
            null !== $data->author && $previous->author !== $data->author ? new Author($data->author) : null,
            null !== $data->content && $previous->content !== $data->content ? new BookContent($data->content) : null,
            null !== $data->price && $previous->price !== $data->price ? new Price($data->price) : null,
        );

        $this->commandBus->dispatch($command);

        /** @var Book $model */
        $model = $this->queryBus->ask(new FindBookQuery(new BookId($id)));

        return BookResource::fromModel($model);
    }
}
