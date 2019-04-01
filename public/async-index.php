<?php

use App\AsyncKernel;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Franzl\Middleware\Whoops\WhoopsRunner;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory as ReactEventLoopFactory;
use React\Http\Server;
use React\Promise\PromiseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;
use Zend\Diactoros\Response\HtmlResponse;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$loop = ReactEventLoopFactory::create();
$kernel = new AsyncKernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG'], $loop);
$kernel->run(8080);
