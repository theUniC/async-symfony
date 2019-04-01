<?php

declare(strict_types=1);

namespace RigorGuild\DomainModel;

use React\Promise\PromiseInterface;

interface Hotels
{
    public function add(Hotel $hotel): PromiseInterface;
    public function byId(int $hotelId): PromiseInterface;
}
