<?php

declare(strict_types=1);

namespace App\Shared\Domain\Repository;

/**
 * @template T of object
 * @template V
 *
 * @implements PaginatorInterface<T>
 */
final class CallbackPaginator implements PaginatorInterface
{
    /** @var V[] */
    private readonly array $results;
    private readonly int $firstResult;
    private readonly int $maxResults;
    private readonly int $totalItems;
    /** @var callable(V): T */
    private $callback;

    /**
     * @param V[]            $results
     * @param callable(V): T $callback
     */
    public function __construct(array $results, int $firstResult, int $maxResults, callable $callback)
    {
        $this->results = $results;
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
        $this->callback = $callback;
        $this->totalItems = \count($results);
    }

    public function getCurrentPage(): int
    {
        if (0 >= $this->maxResults) {
            return 1;
        }

        return (int) floor($this->firstResult / $this->maxResults) + 1;
    }

    public function getLastPage(): int
    {
        if (0 >= $this->maxResults) {
            return 1;
        }

        return (int) ceil($this->totalItems / $this->maxResults) ?: 1;
    }

    public function getItemsPerPage(): int
    {
        return $this->maxResults;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    public function getIterator(): \Traversable
    {
        foreach (array_slice($this->results, $this->firstResult, $this->maxResults) as $key => $result) {
            yield $key => ($this->callback)($result);
        }
    }
}
