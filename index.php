<?php
    namespace cmal\NoApi;
 
    require ('vendor/autoload.php');
    require ('classes/Views.php');
    //require ('classes/backendManager.php');
    require ('classes/TwitterWrapper.php');
    require ('classes/Cache.php');
    
    class Api {
        static $backends = ['twitter' => '\cmal\NoApi\TwitterWrapper'];
        
        public static function dispatch($args) {
            if (isset(self::$backends[$args['backend']])) {
                self::$backends[$args['backend']]::action($args);
            } else {
                die('Silo (backend) not found.');
            }
        }
    }

    $backends = new Api();

    // Below lies the router
    
    $dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '/{backend}/{action}/{query}.{ext}', '\cmal\NoApi\Api::dispatch');
    });

    // Fetch method and URI from somewhere
    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    // Strip query string (?foo=bar) and decode URI
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);

    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
    switch ($routeInfo[0]) {
        case \FastRoute\Dispatcher::NOT_FOUND:
            // ... 404 Not Found
            echo '404';
            break;
        case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            echo 'HTTP Method not allowed';
            break;
        case \FastRoute\Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $vars = $routeInfo[2];
            $handler($vars);
            break;
    }
?>
