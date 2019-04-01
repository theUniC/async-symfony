<?php

declare(strict_types=1);

namespace RigorGuild\Infrastructure\Persistence;

use React\MySQL\ConnectionInterface;
use React\MySQL\Factory;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use RigorGuild\DomainModel\Hotel;
use RigorGuild\DomainModel\Hotels;

final class AsyncMysqlHotels implements Hotels
{
    /**
     * @var Factory
     */
    private $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function add(Hotel $hotel): PromiseInterface
    {
        $connection = $this->factory->createLazyConnection('test:1234Abcd@localhost/test');

        return $connection->query('INSERT INTO hotels (id, name) VALUES (?, ?)', [$hotel->getId(), $hotel->getName()])
                          ->then(static function(QueryResult $queryResult) use ($connection, $hotel) {
                              $connection->quit();
                              return $hotel;
                          });
    }

    public function byId(int $hotelId): PromiseInterface
    {
        $connection = $this->factory->createLazyConnection('test:1234Abcd@localhost/test');

        return $connection->query('SELECT * FROM hotels WHERE id = ?', [$hotelId])
                          ->then(function(QueryResult $queryResult) use ($connection) {
                              $hotel = new Hotel($queryResult->resultRows[0]['id'], $queryResult->resultRows[0]['name']);
                              $connection->quit();
                              return $hotel;
                          });
    }
}
