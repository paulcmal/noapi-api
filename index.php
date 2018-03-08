<?php
    namespace cmal\NoApi;
 
    require (__DIR__ . '/vendor/autoload.php');
    
    use \cmal\Api\Api;
    
    class test {
        public function truc ($args) {
            echo "main index page";
        }
    }
    
    class defaultBackend {
        public function truc ( $args ) {
            echo "default route because your backend was not found";
        }
    }
    
    try {
        new Api([
            "twitter" => ['\cmal\NoApi\Backend\TwitterBackend', 'dispatch'],
            '/' => ['\cmal\NoApi\test', 'truc'],
            '@' => ['\cmal\NoApi\defaultBackend', 'truc']
        ]);
    } catch (\cmal\Api\Exception\BaseRouteMatched $e) {
        echo 'index page';
    } catch (\cmal\Api\Exception\NoRouteMatched $e) {
        echo '404';
    }
