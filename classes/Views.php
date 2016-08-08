<?php
    namespace cmal\NoApi;
    
    class Views {
        static $extensions = ['html' => '\cmal\NoApi\Views::toHTML', 'json' => '\cmal\NoApi\Views::toJSON'];
        
        public static function render($data, $ext) {
            if (isset(self::$extensions[$ext])) {
                self::$extensions[$ext]($data, $ext);
            } else {
                echo 'Extension not supported: ' . $ext;
            }
        }
    
        public static function toJSON($twitter) {
            header('Content-Type: application/json; charset: utf-8');
            echo json_encode($twitter);
        }
        
        public static function toHTML($twitter) {        
            $loader = new \Twig_Loader_Filesystem('./templates');
            $twig = new \Twig_Environment($loader, array(
                'cache' => './cache/twig',
            ));
            echo $twig->render('base.html.twig', $twitter);
        }
    }
?>
