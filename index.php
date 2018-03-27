<?php
    namespace cmal\NoApi;
 
    require (__DIR__ . '/vendor/autoload.php');
    
    use \cmal\Api\Api;
    
    try {
        new Api([
            "twitter" => ['\cmal\NoApi\Backend\TwitterBackend', 'dispatch'],
            "youtube" => ['\cmal\NoApi\Backend\YoutubeBackend', 'dispatch'],
        ]);
    } catch (\cmal\Api\Exception\BaseRouteMatched $e) {
        echo 'index page';
    } catch (\cmal\Api\Exception\NoRouteMatched $e) {
        echo '404';
    }

