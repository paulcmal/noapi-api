<?php
    namespace cmal\NoApi;

    class Cache {
        // Removed ./ in front of cache to be able to return something that'll be urlifyable
        static $folder = 'cache/backends';
        
        // Allowed mimetypes for fetchFile()
        static $mimetypes = [ 'image/gif' => '.gif', 'image/jpeg' => '.jpg', 'image/png' => '.png', 'image/x-icon' => '.ico' ];
        
        public static function keyFile($backend, $key, $extension = '') {
             return self::filePath($backend . '/' . self::key($key) . $extension);
        }
        
        public static function key($key) {
            return hash('sha512', $key);
        }
        
        public static function filePath($filename) {
            return self::$folder . '/' . $filename;
        }
        
        public static function getURL($filepath) {
            $scheme = $_SERVER['HTTPS'] ? 'https://' : 'http://';
            return $scheme . $_SERVER['SERVER_NAME'] . '/' . $filepath;
        }

        public static function get($backend, $key, $timeout) {
            try {
                // If the file does not exist, an exception is thrown
                $cache = new \SplFileObject(self::keyFile($backend, $key), 'r');
                if (time() - $cache->getMTime() > $timeout) {
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
        
        /*
            getPath returns path to cache file or null
                $backend (string)
                $key (string)
        */
        protected static function getPath ($backend, $key) {
            $path = self::$folder . '/' . $backend . '/' . hash('sha512', $key);
            if (!file_exists($path)) {
                //echo 'file ' . $path . ' not found';
                throw new \Exception;
            }
            if (is_link($path)) {
                $link = readlink($path);
                if (!$link) {
                    die('Could not read symlink. Database corrupted?');
                }
                return self::filePath($backend . '/' . $link);
            } else {
                return $path;
            }
        }
        
        /* Caches a remote file locally
            and returns the local path
        */        
        public static function fetchFile ($url) {
            try {
                // If image is already cached, we can get its path like this
                return self::getPath('url', $url);
            } catch (\Exception $e) {
                $content = file_get_contents($url);
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimetype = (new \finfo(FILEINFO_MIME_TYPE))->buffer($content);
                
                // If mimetype is not allowed to be stored, return original url
                if (!isset(self::$mimetypes[$mimetype])) return $url;
                
                try {
                    // Return the path to the image
                    return self::set('url', $url, $content, self::$mimetypes[$mimetype]);
                } catch (\Exception $e) {
                    // File could not be saved to cache, return original url
                    return $url;
                }
            }
        }
        
        public static function set($backend, $key, $v, $extension = '') {
            try {
                $keyFile = self::keyFile($backend, $key);
                $path = $keyFile . $extension;
                $cache = new \SplFileObject($path, 'w');
                $cache->flock(LOCK_EX);
                $cache->fwrite($v);
                $cache->fflush();
                $cache->flock(LOCK_UN);
                if ($extension) {
                    //Need to check is the symlink is set properly
                    $target = self::key($key) . $extension;
                    if (symlink($target, $keyFile) === false) {
                        echo 'Failed to symlink ' . $keyFile . '<br> to ' . $target . '<br><br>';
                        //echo "failed to save symlink";   
                    }
                }
                return $path;
            } catch (\Exception $e) {
                // File could not be written, throw an exception
                throw new \Exception;
            }
        }
    }
?>
