<?php
    
    namespace cmal\NoApi\Backend;
    
    require ('twitter.php');
    
    use \cmal\NoApi\Views;
    use \alct\noapi\Twitter;

    class TwitterWrapper {
        
        static $extensions = ['default' => 'Views::toJSON', 'json' => 'Views::toJSON', 'html' => 'Views::toHTML'];
        /*
            static $actions[] : available actions, in the form of
                [ 'actionname' => 'backendClass::actionMethod' …]
        */
        static $actions = ['user' => 'TwitterWrapper::user', 'search' => 'TwitterWrapper::search', 'tag' => 'TwitterWrapper::tag'];

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
