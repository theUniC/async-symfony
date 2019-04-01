<?php

declare(strict_types=1);

namespace RigorGuild\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use React\Promise\PromiseInterface;
use function React\Promise\reject;
use function React\Promise\resolve;
use RigorGuild\DomainModel\Hotel;
use RigorGuild\DomainModel\Hotels;

final class DoctrineOrmHotels implements Hotels
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(Hotel $hotel): PromiseInterface
    {
        $this->entityManager->persist($hotel);
        $this->entityManager->flush();

        return resolve($hotel);
    }

    public function byId(int $hotelId): PromiseInterface
    {
        return resolve(
            $this->entityManager->find(Hotel::class, $hotelId)
        );
    }
}
