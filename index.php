<?php
    namespace cmal\NoApi;
 
    require ('vendor/autoload.php');

    require ('classes/Views.php');
    require ('classes/TwitterWrapper.php');
    require ('classes/Cache.php');
    
    class Api {
        /*
            static $backends[] : available backends, in the form of
                [ 'backendname' => '\cmal\NoApi\Backend\backendClass' …]
        */
        static $backends = ['twitter' => '\cmal\NoApi\Backend\TwitterWrapper'];
        
        /*
            dispatch : calls backend action method
                $args[]
                    backend: string, backend name to forward the request to
                    action: string, backend method requested
                    query: string, parameters
            
            Available actions are defined in the backends
        */
        public static function dispatch($args) {
            $backend = $args['backend'];
            if (isset(self::$backends[$backend])) {
                // If the backend exists, we need to check if it has a corresponding action
                if (isset(self::$backends[$backend]::$actions[$args['action']])) {
                    // Both backend and action requested are valid, so we forward the query to the appropriate method
                    call_user_func(self::$backends[$backend] . '::' . $args['action'], $args);
                } else {
                    // The requested action is not registered in the backend
                    die('Action not found.');
                }
            } else {
                // The requested backend does not exist
                die('Silo (backend) not found.');
            }
        }
    }

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
