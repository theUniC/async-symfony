<?php

namespace App\Controller\Sync;

use React\Promise\Promise;
use React\Promise\PromiseInterface;
use RigorGuild\Command\NewHotelCommand;
use RigorGuild\DomainModel\Hotel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class NewHotelController extends AbstractController
{
    /**
     * @Route("/sync/hotel", name="sync_new_hotel")
     */
    public function __invoke(
        Request $request,
        MessageBusInterface $bus
    ) {
        $envelope = $bus->dispatch(
            new NewHotelCommand(
                $request->query->getInt('id'),
                $request->query->get('name')
            )
        );

        /** @var HandledStamp $handledStamp */
        $handledStamp = $envelope->last(HandledStamp::class);

        /** @var Promise $promise */
        $promise = $handledStamp->getResult();

        $response = null;
        $promise->done(static function(Hotel $hotel) use (&$response) {
            $response = $hotel;
        });

        return $this->json($response);
    }
}
