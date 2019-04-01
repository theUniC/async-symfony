<?php

declare(strict_types=1);

namespace RigorGuild\Command;

use React\Promise\PromiseInterface;
use RigorGuild\DomainModel\Hotel;
use RigorGuild\DomainModel\Hotels;

final class NewHotelCommandHandler
{
    /**
     * @var Hotels
     */
    private $hotels;

    public function __construct(Hotels $hotels)
    {
        $this->hotels = $hotels;
    }

    public function __invoke(NewHotelCommand $command): PromiseInterface
    {
        $hotel = new Hotel($command->id(), $command->name());

        return $this->hotels->add($hotel);
    }
}
