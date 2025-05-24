<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ApiPlatform\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BookStore\Domain\Command\DiscountBookCommand;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\Query\FindBookQuery;
use App\BookStore\Domain\ValueObject\BookId;
use App\BookStore\Domain\ValueObject\Discount;
use App\BookStore\Infrastructure\ApiPlatform\Payload\DiscountBookPayload;
use App\BookStore\Infrastructure\ApiPlatform\Resource\BookResource;
use App\Shared\Application\Command\CommandBusInterface;
use App\Shared\Application\Query\QueryBusInterface;
use Webmozart\Assert\Assert;

final readonly class DiscountBookProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): BookResource
    {
        Assert::isInstanceOf($data, DiscountBookPayload::class);

        $bookResource = $context['previous_data'] ?? null;
        Assert::isInstanceOf($bookResource, BookResource::class);

        $command = new DiscountBookCommand(
            new BookId((string) $bookResource->id),
            new Discount($data->discountPercentage),
        );

        $this->commandBus->dispatch($command);

        /** @var Book $model */
        $model = $this->queryBus->ask(new FindBookQuery($command->id));

        return BookResource::fromModel($model);
    }
}
