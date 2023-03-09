<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Projection;

use Ecotone\EventSourcing\Attribute\ProjectionStateGateway;

interface BooksByAuthorGateway
{
    /**
     * @return array<string, list<string>>
     */
    #[ProjectionStateGateway(BooksByAuthorProjection::NAME)]
    public function getByAuthorBookIds(): array;
}
