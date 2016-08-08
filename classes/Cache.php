<?php
    namespace cmal\NoApi;

    class Cache {
        static $folder = './cache/backends';
        static $timeout = 300;
        
        public static function keyFile($backend, $key) {
            return self::$folder . '/' . $backend . '/' . hash('sha512', $key);
        }

        public static function get($backend, $key) {
            try {
                // If the file does not exist, an exception is thrown
                $cache = new \SplFileObject(self::keyFile($backend, $key), 'r');
                if (time() - $cache->getMTime() > self::$timeout) {
                    // If the cache is too old, force refresh
                    throw new \Exception();
                }
                // Use content from the file cache
                $content = $cache->fread($cache->getSize());
                return $content;
            } catch (\Exception $e) {
                // Either cache key does not exist or has expired
                throw new \Exception;
            }
        }
        
        public static function set($backend, $key, $v) {
            try {
                $cache = new \SplFileObject(self::keyFile($backend, $key), 'w');
                $cache->flock(LOCK_EX);
                $cache->fwrite($v);
                $cache->fflush();
                $cache->flock(LOCK_UN);
                return true;
            } catch (\Exception $e) {
                // File could not be written, throw an exception
                throw new \Exception;
            }
        }
    }
?>
