<?php

namespace App\Controller\Sync;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use RigorGuild\Command\GetHotelCommand;
use RigorGuild\Command\GetHotelCommandHandler;
use RigorGuild\DomainModel\Hotel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class GetHotelController extends AbstractController
{
    /**
     * @Route("/sync/hotel/{id}", name="get_hotel")
     */
    public function __invoke(int $id, MessageBusInterface $bus, SerializerInterface $serializer): Response
    {
        $envelope = $bus->dispatch(new GetHotelCommand($id));

        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var Promise $promise */
        $promise = $handledStamp->getResult();

        $response = null;
        $promise->done(static function(Hotel $hotel) use(&$response) {
            $response = $hotel;
        });


        return $this->json($response);
    }
}
