<?php
    
    namespace cmal\NoApi\Backend;
    
    use \cmal\NoApi\Views;
    use \cmal\Api\Backend;
    use \cmal\Twitter\Twitter;

    class TwitterBackend extends \cmal\Api\Backend {
    
        static $routes = [
		    'GET' => [
			    '/{action}/{query:.+}.{ext}' => ['\cmal\NoApi\Backend\TwitterBackend', 'action'],
			    '/{query}.{ext}' => ['\cmal\NoApi\Backend\TwitterBackend', 'getUser']
		    ],
	    ];
        
        static $extensions = ['json' => 'Views::toJSON', 'html' => 'Views::toHTML'];

        static $actions = [
            'user' => 'user',
            'search' => 'search',
            'tag' => 'tag',
            'hashtag' => 'tag'
        ];
               
        public function dispatch ($args, $routes = NULL) {
		    parent::dispatch($args, self::$routes);
	    }
	    
	    /*
	        Here no action was specified, however we mean to get a user
	    */
	    public static function getUser($args) {
	        $args['action'] = 'user';
	        return self::action($args);
	    }
	    
	    public static function action ($args) {
	        if (array_key_exists($args['action'], self::$actions)) {
	        #if (isset(self::$actions[$args['action']])) {
	            try {
	                $meta = call_user_func(['\cmal\NoApi\Backend\TwitterBackend', self::$actions[$args['action']]], $args);
	                // Cache responses for 60 seconds
                    $parsed = (new Twitter(60))->fromMeta($meta);
                    Views::render($parsed, $args['ext']);
	            } catch (Exception $e) {
	                echo "Action failed with error message : " . $e;
	            }
	        } else {
	            echo "Action not found.";
	        }
	    }
        
        public static function user($args) {
            return [
                'type' => 'user',
                'query' => $args['query'],
                'url' => 'https://twitter.com/' . $args['query']
            ];
        }
        
        public function search($args) {
            $query = str_replace(' ', '+', $args['query']);
            return [
                'type' => 'search',
                'query' => $query,
                'url' => 'https://twitter.com/search?f=tweets&q=' . $query
            ];
        }
        
        public function tag($args) {
            return [
                'type' => 'hashtag',
                'query' => $args['query'],
                'url' => 'https://twitter.com/hashtag/' . $args['query'] . '?f=tweets'
            ];
        }
    }

?>
