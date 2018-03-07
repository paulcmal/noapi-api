<?php
    namespace cmal\NoApi;
 
    require (__DIR__ . '/vendor/autoload.php');
    
    use \cmal\Api\Api;
    
    new Api(["twitter" => ['\cmal\NoApi\Backend\TwitterBackend', 'dispatch']]);

?>

