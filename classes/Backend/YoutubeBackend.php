<?php
    
    namespace cmal\NoApi\Backend;
    
    use \cmal\NoApi\Views;
    use \cmal\Api\Router;
    use \cmal\Api\Exception\NoRouteMatched;
    use \cmal\Api\Exception\BaseRouteMatched;
    use \cmal\NoApi\RemoteFileCache;
    
    use Fusonic\OpenGraph\Consumer;
    use pastuhov\Command\Command;

    class YoutubeBackend {
    
        static $routes = [
		    'GET' => [
		        '/preview/{youtubeid:[a-zA-Z0-9_-]{11}}[/{quality:\d+}]' => ['\cmal\NoApi\Backend\YoutubeBackend', 'previewImg'],
		    ],
	    ];
               
        public function dispatch ($args) {
            try {
                new Router (self::$routes, $args);
		    } catch (BaseRouteMatched $e){
		        echo 'Youtube backend index.';
		    } catch (NoRouteMatched $e) {
		        echo 'No such Youtube action.';
		    }
	    }
	    
	    public function replyContent ($content, $error = NULL) {
	        $dict = isset($error) ? ['status' => 'error', 'content' => $content]
	            : ['status' => 'ok', 'content' => $content];
	        return json_encode($dict);
	    }
	    
	    public function previewImg ($args) {
	        if (!isset ($args['quality']) {
	            $args['quality'] = 30;
	        }
	    
            $consumer = new Consumer();
            $cacheDir = 'cache/noapi';
            $url = "https://www.youtube.com/watch?v=" . $args['youtubeid'];
            // Short hack: with fetchFileURL instead of passing the body
            $cachedURL = 'http:' . RemoteFileCache::fetchFileURL($url, $cacheDir);
            $object = $consumer->loadUrl($cachedURL);
            
            if (!isset($object->images[0])) {
                echo self::replyContent('Video not found', true);
                return;
            }
            
            $inputFile = RemoteFileCache::fetchFile($object->images[0]->url, $cacheDir);
            
            $image = new \Imagick($inputFile);
            $image->setImageCompressionQuality($args['quality']);
            $image->thumbnailImage(512, 0);
            $object->previewDim = ['w' => $image->getImageWidth(), 'h' => $image->getImageHeight()];
            $object->image64 = base64_encode($image);
            
            echo self::replyContent($object);
	    }
	    
    }

?>
