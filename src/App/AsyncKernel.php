<?php

namespace App;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server;
use React\Promise\PromiseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Zend\Diactoros\Response\HtmlResponse;
use React;

class AsyncKernel extends Kernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(string $environment, bool $debug, LoopInterface $loop)
    {
        $this->loop = $loop;

        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }

    protected function initializeContainer()
    {
        parent::initializeContainer();

        $this->container->set('event_loop', $this->loop);
    }

    protected function getHttpKernel()
    {
        return $this->container->get('promised_http_kernel');
    }

    public function run(int $port): void
    {
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        $httpFoundationFactory = new HttpFoundationFactory();

        $this->boot();

        $server = new Server([
            function(ServerRequestInterface $request, callable  $next) {
                echo $request->getMethod() . ' ' . $request->getUri() . PHP_EOL;

                if ('dev' !== $this->getEnvironment()) {
                    return $next($request);
                }

                $handler = new ExceptionHandler(true);

                try {
                    return $next($request);
                } catch (HttpException $e) {
                    ob_start();
                    $handler->handle($e);
                    return new HtmlResponse(ob_get_clean(), $e->getStatusCode(), $e->getHeaders());
                } catch (Exception $e) {
                    ob_start();
                    $handler->handle($e);
                    return new HtmlResponse(ob_get_clean(), 500);
                }
            },
            function (ServerRequestInterface $request) use ($psrHttpFactory, $httpFoundationFactory): PromiseInterface {
                $symfonyRequest = $httpFoundationFactory->createRequest($request);
                /** @var PromiseInterface $promise */
                $promise = $this->handle($symfonyRequest);

                return $promise->then(
                    function(Response $response) use ($psrHttpFactory, $symfonyRequest) {
                        $this->terminate($symfonyRequest, $response);
                        return $psrHttpFactory->createResponse($response);
                    }
                );
            }
        ]);

        $server->on('error', function(Exception $e) {
            dump($e);
        });

        $socket = new React\Socket\Server($port, $this->loop);
        $server->listen($socket);

        $this->loop->run();
    }
}
