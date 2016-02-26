<?php


namespace Laasti\LeanApp;

use Laasti\Directions\Route;
use Laasti\Http\Application as CoreApp;
use Laasti\Http\HttpKernel;
use League\Container\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;

class Application
{
    protected $coreApp;

    public function __construct(CoreApp $app)
    {
        $this->coreApp = $app;
    }

    public static function create()
    {
        $container = new Container;
        $container->share('container', $container);
        $container->share('Interop\Container\ContainerInterface', $container);
        $container->share('error_formatter', 'Laasti\Core\Exceptions\PrettyBooBooFormatter')->withArguments([[], new HttpKernel(function() {}), New ServerRequest(), new Response]);
        $container->add('config', [
            'booboo' => [
                'pretty_page' => 'error_formatter',
                //How errors are displayed in the output
                'formatters' => [
                    'League\BooBoo\Formatter\HtmlTableFormatter' => E_ALL
                ],
                //How errors are handled (logging, sending e-mails...)
                'handlers' => [
                    'League\BooBoo\Handler\LogHandler'
                ]
            ],
            'peels' => [
                'http' => [
                    'runner' => 'Laasti\Peels\Http\HttpRunner',
                    'middlewares' => []
                ]
            ],
            'directions' => [
                'default' => [
                    'strategy' => 'Laasti\Directions\Strategies\PeelsStrategy',
                    'routes' => []
                ]
            ]
        ]);
        $container->addServiceProvider('Laasti\Directions\Providers\LeagueDirectionsProvider');
        $container->addServiceProvider('Laasti\Peels\Providers\LeaguePeelsProvider');
        $container->addServiceProvider('Laasti\Core\Providers\MonologProvider');
        $kernel = new HttpKernel(function($request, $response) use ($container) {
           $runner = $container->get('peels.http')->create();
           return $runner($request, $response);
        });
        $container->share('kernel');
        $coreApp = new CoreApp($container, $kernel);

        return new static($coreApp);
    }

    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {
        if (is_null($request)) {
            $request = ServerRequestFactory::fromGlobals();
        }
        if (is_null($response)) {
            $response = new Response;
        }
        $this->getContainer()->share('request', $request);
        $this->getContainer()->share('response', $response);

        $this->coreApp->run($request, $response);
    }
    
    public function getContainer()
    {
        return $this->coreApp->getContainer();
    }

    public function getConfigArray()
    {
        return $this->coreApp->getConfigArray();
    }

    public function getConfig($key, $default = null)
    {
        return $this->coreApp->getConfig($key, $default);
    }

    public function setConfig($key, $value)
    {
        return $this->coreApp->setConfig($key, $value);
    }

    public function getLogger()
    {
        return $this->coreApp->getLogger();
    }

    public function middleware($middleware)
    {
        $this->getContainer()->get('peels.http')->push($middleware);
        return $this;
    }

    /**
     *
     * @param type $method
     * @param type $route
     * @param type $callable
     * @return Route
     */
    public function route($method, $route, $callable)
    {
        return $this->getContainer()->get('directions.default')->add($method, $route, $callable);
    }

    public function handle($error, $callable)
    {
        $this->getContainer()->get('error_formatter')->add($error, $callable);
        return $this;
    }
}
