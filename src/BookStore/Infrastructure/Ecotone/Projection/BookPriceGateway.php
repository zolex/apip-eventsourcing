<?php

declare(strict_types=1);

namespace App\BookStore\Infrastructure\Ecotone\Projection;

use Ecotone\EventSourcing\Attribute\ProjectionStateGateway;

interface BookPriceGateway
{
    /**
     * @return array<string, int>
     */
    #[ProjectionStateGateway(BookPriceProjection::NAME)]
    public function getBookPriceList(): array;
}
