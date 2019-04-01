<?php

namespace App\Controller\Async;

use React\Promise\PromiseInterface;
use RigorGuild\Command\NewHotelCommand;
use RigorGuild\Command\NewHotelCommandHandler;
use RigorGuild\DomainModel\Hotel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class NewHotelController extends AbstractController
{
    /**
     * @Route("/hotel", name="new_hotel")
     */
    public function __invoke(Request $request, NewHotelCommandHandler $newHotelCommandHandler, SerializerInterface $serializer): PromiseInterface
    {
        $promise = $newHotelCommandHandler(new NewHotelCommand($request->query->getInt('id'), $request->query->get('name')));

        return $promise->then(function(Hotel $hotel) use($serializer) {
            return $this->json(
                $serializer->serialize($hotel, 'json'),
                Response::HTTP_CREATED
            );
        });
    }
}
