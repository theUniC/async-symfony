<?php

declare(strict_types=1);

namespace RigorGuild\Command;

final class GetHotelCommand
{
    /**
     * @var int
     */
    private $hotelId;

    public function __construct(int $hotelId)
    {
        $this->hotelId = $hotelId;
    }

    public function hotelId(): int
    {
        return $this->hotelId;
    }
}
