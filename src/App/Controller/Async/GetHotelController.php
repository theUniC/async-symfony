<?php

declare(strict_types = 1);

namespace App\Controller\Async;

use React\Promise\PromiseInterface;
use RigorGuild\Command\GetHotelCommand;
use RigorGuild\DomainModel\Hotel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

class GetHotelController extends AbstractController
{
    /**
     * @Route("/async/hotel/{id}", name="async_get_hotel")
     */
    public function __invoke(int $id, MessageBusInterface $bus): PromiseInterface
    {
        $envelope = $bus->dispatch(new GetHotelCommand((int) $id));

        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var PromiseInterface $promise */
        $promise = $handledStamp->getResult();

        return $promise->then(function(Hotel $hotel) {
            return $this->json($hotel);
        });
    }
}
