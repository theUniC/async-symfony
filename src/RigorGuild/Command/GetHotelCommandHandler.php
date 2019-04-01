<?php

declare(strict_types=1);

namespace RigorGuild\Command;

use React\Promise\PromiseInterface;
use RigorGuild\DomainModel\Hotels;

final class GetHotelCommandHandler
{
    /**
     * @var Hotels
     */
    private $hotels;

    public function __construct(Hotels $hotels)
    {
        $this->hotels = $hotels;
    }

    public function __invoke(GetHotelCommand $command): PromiseInterface
    {
        return $this->hotels->byId($command->hotelId());
    }
}
