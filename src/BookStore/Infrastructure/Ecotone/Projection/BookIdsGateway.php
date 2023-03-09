<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Projection;

use Ecotone\EventSourcing\Attribute\ProjectionStateGateway;

interface BookIdsGateway
{
    /**
     * @return list<string>
     */
    #[ProjectionStateGateway(BookIdsProjection::NAME)]
    public function getBookIds(): array;
}
