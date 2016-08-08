<?php
    
    namespace cmal\NoApi;
    
    require ('twitter.php');
    
    use \cmal\NoApi\Views;
    use \alct\noapi\Twitter;

    class TwitterWrapper {
        
        static $extensions = ['default' => 'Views::toJSON', 'json' => 'Views::toJSON', 'html' => 'Views::toHTML'];     
        // Actions can be linked to a method with a different name
        static $actions = ['user' => 'user', 'search' => 'search', 'tag' => 'tag'];
        
        /*
            $args
                backend: name of the social backend called
                action: name of the action (such as user/search/tag)
                query: parameter passed to the action
        */
        public static function action($args) {
            if (isset(self::$actions[$args['action']])) {
                call_user_func('self::' . $args['action'], $args);
            } else {
                die('Action not found.');
            }
        }

        public static function user($args) {
            $query = $args['query'];
            $meta = ['type' => 'user', 'query' => $query, 'url' => 'https://twitter.com/' . $query];
            $twittie = (new Twitter())->twitter($meta);
            //self::render($twittie, $args['ext']);
            \cmal\NoApi\Views::render($twittie, $args['ext']);
        }
        
        public function search($args) {
            $query = str_replace(' ', '+', $args['query']);
            $meta = ['type' => 'search', 'query' => $query, 'url' => 'https://twitter.com/search?f=tweets&q=' . $query];
            $twittie = (new Twitter())->twitter($meta);
            \cmal\NoApi\Views::render($twittie, $args['ext']);
        }
        
        public function tag($args) {
            $query = $args['query'];
            $meta = ['type' => 'hashtag', 'query' => $query, 'url' => 'https://twitter.com/hashtag/' . $query . '?f=tweets'];
            $twittie = (new Twitter())->twitter($meta);
            \cmal\NoApi\Views::render($twittie, $args['ext']);
        }
    }

?>
