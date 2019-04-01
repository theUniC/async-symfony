<?php

namespace App\Controller\Async;

use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(LoopInterface $loop): PromiseInterface
    {
        $deferred = new Deferred();

        $loop->futureTick(function() use ($deferred) {
            $deferred->resolve(
                $this->json([
                    'message' => 'Welcome to your new controller!',
                    'path' => 'src/Controller/DefaultController.php',
                ])
            );
        });

        return $deferred->promise();
    }
}
