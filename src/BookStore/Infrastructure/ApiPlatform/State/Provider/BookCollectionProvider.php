<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\PaginatorInterface as ApiPlatformPaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Domain\Query\FindBooksQuery;
use App\BookStore\Domain\ValueObject\Author;
use App\BookStore\Infrastructure\ApiPlatform\Resource\BookResource;
use App\Shared\Application\Query\QueryBusInterface;
use App\Shared\Domain\Repository\PaginatorInterface;
use App\Shared\Infrastructure\ApiPlatform\State\Paginator;

/**
 * @implements ProviderInterface<BookResource>
 */
final readonly class BookCollectionProvider implements ProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private Pagination $pagination,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ApiPlatformPaginatorInterface|array
    {
        /** @var string|null $author */
        $author = $context['filters']['author'] ?? null;
        $offset = $limit = null;

        if ($this->pagination->isEnabled($operation, $context)) {
            $offset = $this->pagination->getPage($context);
            $limit = $this->pagination->getLimit($operation, $context);
        }

        /** @var iterable<Book>|PaginatorInterface<Book> $books */
        $books = $this->queryBus->ask(new FindBooksQuery(null !== $author ? new Author($author) : null, $offset, $limit));

        $resources = [];
        foreach ($books as $book) {
            $resources[] = BookResource::fromModel($book);
        }

        if (null !== $offset && null !== $limit && $books instanceof PaginatorInterface) {
            return new Paginator(
                new \ArrayIterator($resources),
                (float) $books->getCurrentPage(),
                (float) $books->getItemsPerPage(),
                (float) $books->getLastPage(),
                (float) $books->getTotalItems(),
            );
        }

        return $resources;
    }
}
