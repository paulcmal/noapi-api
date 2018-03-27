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
		        '/preview/{youtubeid:[a-zA-Z0-9_-]{11}}' => ['\cmal\NoApi\Backend\YoutubeBackend', 'previewImg'],
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
	    
	    public function findWH ($content) {
	        $matches = [];
	        preg_match_all('<svg.*width="(.*)".*height="(.*)">', $content, $matches);
	        return ['w' => $matches[1][0], 'h' => $matches[2][0]];
	    }
	    
	    public function previewImg ($args) {
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
            
            $inputFile = RemoteFileCache::fileName($object->images[0]->url, $cacheDir);
            $outputFile = $inputFile . '.svg';
            //$outputFile = RemoteFileCache::fileName($object->images[0]->url, $cacheDir) . '.svg';
            
            if (is_file($outputFile)) {
                $handle = fopen($outputFile, 'r');
                $contents = fread($handle, filesize($outputFile));
                $object->preview = $contents;
                $object->preview64 = base64_encode($contents);
                $object->previewDim = self::findWH($contents);
                $image = new \Imagick($inputFile);
                $image->setImageCompressionQuality(30);
                $image->thumbnailImage(512, 0);
                $object->image64 = base64_encode($image);
                echo self::replyContent($object);
                fclose($handle);
                return;
            }
            
            $img = RemoteFileCache::fetchFile($object->images[0]->url, $cacheDir);
            
            try {
                $primitive = realpath('./tools/primitive');
                Command::exec($primitive . ' -i {input} -o {output} -n 50 -s 512',
                    [
                        'input' => $img,
                        'output' => $outputFile,
                    ]
                );        
            } catch (\pastuhov\Command\CommandException $e) {
                echo self::replyContent('Primitive exception: ' . $e, true);
                return;
            }
            
            $handle = fopen($outputFile, "r");
            $contents = fread($handle, filesize($outputFile));
            $object->preview = $contents;
            $object->preview64 = base64_encode($contents);
            $object->previewDim = self::findWH($contents);
            $image = new \Imagick($inputFile);
            $image->setImageCompressionQuality(30);
            $image->thumbnailImage(512, 0);
            $object->image64 = base64_encode($image);
            echo self::replyContent($object);
            fclose($handle);
	    }
	    
    }

?>
