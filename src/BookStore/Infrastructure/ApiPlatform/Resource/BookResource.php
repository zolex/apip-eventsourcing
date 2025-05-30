<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use App\BookStore\Domain\Command\AnonymizeBooksCommand;
use App\BookStore\Domain\Event\BookEvent;
use App\BookStore\Domain\Model\Book;
use App\BookStore\Infrastructure\ApiPlatform\OpenApi\AuthorFilter;
use App\BookStore\Infrastructure\ApiPlatform\Payload\DiscountBookPayload;
use App\BookStore\Infrastructure\ApiPlatform\State\Processor\AnonymizeBooksProcessor;
use App\BookStore\Infrastructure\ApiPlatform\State\Processor\CreateBookProcessor;
use App\BookStore\Infrastructure\ApiPlatform\State\Processor\DeleteBookProcessor;
use App\BookStore\Infrastructure\ApiPlatform\State\Processor\DiscountBookProcessor;
use App\BookStore\Infrastructure\ApiPlatform\State\Processor\UpdateBookProcessor;
use App\BookStore\Infrastructure\ApiPlatform\State\Provider\BookCollectionProvider;
use App\BookStore\Infrastructure\ApiPlatform\State\Provider\BookEventsProvider;
use App\BookStore\Infrastructure\ApiPlatform\State\Provider\BookItemProvider;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    shortName: 'Book',
    operations: [
        // queries
        new GetCollection(
            '/books/cheapest.{_format}',
            openapi: new Operation(summary: 'Create a rabbit picture'),
        ),
        new GetCollection(
            '/books/{id}/events.{_format}',
            openapi: new Operation(summary: 'Get events for this Book.'),
            output: BookEvent::class,
            provider: BookEventsProvider::class,
        ),

        // commands
        new Post(
            '/books/anonymize.{_format}',
            status: 202,
            openapi: new Operation(summary: 'Anonymize author of every Book resources.'),
            input: AnonymizeBooksCommand::class,
            output: false,
            processor: AnonymizeBooksProcessor::class,
        ),
        new Post(
            '/books/{id}/discount.{_format}',
            openapi: new Operation(summary: 'Apply a discount percentage on a Book resource.'),
            input: DiscountBookPayload::class,
            provider: BookItemProvider::class,
            processor: DiscountBookProcessor::class,
        ),

        // basic crud
        new GetCollection(
            filters: [AuthorFilter::class],
            provider: BookCollectionProvider::class,
        ),
        new Get(
            provider: BookItemProvider::class,
        ),
        new Post(
            validationContext: ['groups' => ['create']],
            processor: CreateBookProcessor::class,
        ),
        new Put(
            provider: BookItemProvider::class,
            processor: UpdateBookProcessor::class,
            extraProperties: ['standard_put' => true],
        ),
        new Patch(
            provider: BookItemProvider::class,
            processor: UpdateBookProcessor::class,
        ),
        new Delete(
            provider: BookItemProvider::class,
            processor: DeleteBookProcessor::class,
        ),
    ],
)]
final class BookResource
{
    public function __construct(
        #[ApiProperty(readable: false, writable: false, identifier: true)]
        public ?AbstractUid $id = null,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\Length(min: 1, max: 255, groups: ['create', 'Default'])]
        public ?string $name = null,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\Length(min: 1, max: 1023, groups: ['create', 'Default'])]
        public ?string $description = null,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\Length(min: 1, max: 255, groups: ['create', 'Default'])]
        public ?string $author = null,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\Length(min: 1, max: 65535, groups: ['create', 'Default'])]
        public ?string $content = null,

        #[Assert\NotNull(groups: ['create'])]
        #[Assert\PositiveOrZero(groups: ['create', 'Default'])]
        public ?int $price = null,
    ) {
    }

    public static function fromModel(Book $book): static
    {
        return new self(
            Uuid::fromString($book->id()->value),
            $book->name()->value,
            $book->description()->value,
            $book->author()->value,
            $book->content()->value,
            $book->price()->amount,
        );
    }
}
