<?php
    namespace cmal\NoApi;
    
    use \Apix\Cache;

    class RemoteFileCache {
        
        public function fileName ($url) {
            $path = explode ('.', $url);
            $ext = array_pop( $path );
            return hash('sha256', $url) . '.' . $ext;
        }
        
        /*
            Fetches a remote file and stores in in $directory
            Filename is hashed but extension is kept
            
            If no directory is provided, the default temp directory
            (usually /tmp) will be used.
        */        
        public function fetchFile ($url, $directory = NULL) {
            if (!isset($directory)) {
                $directory = sys_get_temp_dir() . '/noapi';
            }
            
            
            if (! is_dir($directory)) mkdir($directory, 0755, true);
            if (! is_writable($directory)) die('The cache/img directory is not writable.' . PHP_EOL);
            
            $fileName = $directory . '/' . self::fileName($url);
            
            if (! is_file($fileName)) {
                $image = file_get_contents($url);
                if (file_put_contents($fileName, $image) === false) return ;
            }
        
            return $fileName;    

        }
        
        /*
            Requires a directory in your webroot, otherwise it won't work!
            
            Wraps fetchFile to provide a URL to the file you want to fetch
        */
        public function fetchFileURL ($url, $directory) {
            $localFile = self::fetchFile($url, $directory);
            return $localFile ? '//' . $_SERVER['SERVER_NAME'] . '/' . $localFile : false;
        }
    }
?>
