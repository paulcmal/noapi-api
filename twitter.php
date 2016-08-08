<?php namespace alct\noapi;
class Twitter
{
    
    /**
     * Add HTML markup around @mentions, #hashtags and URLs ; convert \n to <br/>.
     *
     * @param string $string
     *
     * @return string
     */
    protected function html($string)
    {
        $pattern = [
            '~(https?://(?:w{3}\d*\.)?([^\s]+))~i' => '<a href="$1" class="url">$2</a>',
            '~(?<=[^\w]|^)@(\w+)(?=[^\w]|$)~i' => '<a href="https://twitter.com/$1" class="mention">@<span>$1</span></a>',
            '~(?<=[^\w]|^)#(\w+)(?=[^\w]|$)~iu' => '<a href="https://twitter.com/hashtag/$1" class="hashtag">#<span>$1</span></a>',
            '~\n~' => '<br/>',
        ];
        return preg_replace(array_keys($pattern), array_values($pattern), $string);
    }
    /**
     * Clean Twitter specific text quirks.
     *
     * @param string $string
     *
     * @return string
     */
    protected function cleanup($string)
    {
        $pattern = [
            '~pic\.twitter\.com~iU'    => 'https://pic.twitter.com',
            '~\xc2\xa0~'               => ' ',   // replace non breaking spaces by simple ones
            '~(?<!^)(https?://)~'      => ' $1', // make sure every URL is preceeded by a whitespace
            '~\s{2,}~U'                => ' ',   // replace consecutive spaces by a single one
            '~(https?://[^\s]+) ?…~iU' => '$1',  // remove elipsis after URLs
        ];
        return preg_replace(array_keys($pattern), array_values($pattern), $string);
    }
    /**
     * Extract a series of information from a twitter page.
     *
     * @see doc/Twitter.md for the detailed structure of the returned array
     * @see Twitter::query_to_meta() for details about the $meta array
     *
     * @param string $page content of a twitter page
     * @param array  $meta
     *
     * @return array|bool false on error
     */
    protected function parse($page, $meta)
    {
        $twitter['meta'] = $meta;
        $twitter['meta']['backend'] = 'twitter';
        $tweet = '//ol[@id="stream-items-id"]/li[@data-item-type="tweet"]';
        // foo_bar will be converted to foo['bar']
        $tweet_details = [
            'user_avatar'   => './/img[contains(@class, "avatar")]/@src',
            'user_fullname' => './/@data-name',
            'user_name'     => './/@data-screen-name',
            'stats_fav'     => './/span[contains(@class, "ProfileTweet-action--favorite")]//@data-tweet-stat-count',
            'stats_rt'      => './/span[contains(@class, "ProfileTweet-action--retweet")]//@data-tweet-stat-count',
            'id'            => './/@data-tweet-id',
            'retweetid'     => './/@data-retweet-id',
            'text'          => './/p[contains(@class, "tweet-text")]',
            'datetime'      => './/@data-time',
        ];
        $user_stats = '//ul[contains(@class, "ProfileNav-list")]';
        $user_stats_details = [
            'followers' => './/li[contains(@class, "ProfileNav-item--followers")]/a/@title',
            'following' => './/li[contains(@class, "ProfileNav-item--following")]/a/@title',
            'likes'     => './/li[contains(@class, "ProfileNav-item--favorites")]/a/@title',
            'lists'     => './/li[contains(@class, "ProfileNav-item--lists")]/a/@title',
            'tweets'    => './/li[contains(@class, "ProfileNav-item--tweets")]/a/@title',
        ];
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadHTML($page);
        $xpath = new \DomXpath($dom);
        foreach ($xpath->query($tweet) as $item) {
            foreach ($tweet_details as $key => $query) {
                $value = @$xpath->query($query, $item)->item(0)->textContent;
                if ($key == 'retweetid') {
                    $details['retweet'] = empty($value) ? 0 : 1;
                    continue;
                }
                if ($key == 'text') {
                    $value = self::cleanup($value);
                    $value = htmlspecialchars($value, ENT_NOQUOTES);
                    $details['text']['raw'] = $value;
                    $details['text']['html'] = self::html($value);
                    $details['reply'] = substr($value, 0, 1) === '@' ? 1 : 0;
                    continue;
                }
                if ($key == 'datetime') $value = date('c', $value);
                if (strpos($key, '_') !== false) {
                    $subarray = explode('_', $key);
                    $subname  = $subarray[0];
                    $subkey   = $subarray[1];
                    $details[$subname][$subkey] = $value;
                } else {
                    $details[$key] = $value;
                }
            }
            $details['user']['url'] = 'https://twitter.com/' . $details['user']['name'];
            $details['url'] = $details['user']['url'] . '/status/' . $details['id'];
            // type casting
            $details['id'] = (int) $details['id'];
            $details['stats']['fav'] = (int) $details['stats']['fav'];
            $details['stats']['rt']  = (int) $details['stats']['rt'];
            $details['user']['avatar'] = \cmal\NoApi\Cache::fetchFile($details['user']['avatar']);
            ksort($details);
            $twitter['tweets'][] = $details;
        }
        $twitter['tweets_count'] = count($twitter['tweets']);
        if ($meta['type'] == 'user') {
            $item = $xpath->query($user_stats)->item(0);
            foreach ($user_stats_details as $key => $query) {
                $value = @$xpath->query($query, $item)->item(0)->textContent;
                $twitter['user_stats'][$key] = preg_match('~([\d,]+)~', $value, $matches) ? (int) strtr($matches[1], [',' => '']) : 0;
            }
        }
        ksort($twitter);

        return $twitter;
    }

    /**
     * Download and parse the twitter page corresponding to a query.
     *
     * @see doc/Twitter.md
     * @see Twitter::query_to_meta()
     *
     * @param string $query
     *
     * @return array|bool false on error
     */
    /*public function twitter($meta)
    {
        if (! $page = $this->curl($meta['url'])) return false;
        return $this->parse($page, $meta);
    }*/
    
    public static function twitter($meta)
    {
        try {
            // 300 seconds (5 minutes) cache for Twitter pages
            $content = \cmal\NoApi\Cache::get('url', $meta['url'], 300);
        } catch (\Exception $e) {
            // Get data from remote URL
            $content = self::curl($meta['url']);
            // Save data to cache
            try {
                \cmal\NoApi\Cache::set('url', $meta['url'], $content);
            } catch (\Exception $e) {
                // Cache not working. Should not be happening.
            }
        }
        
        // Return the parsed content, or false if the content is empty
        return $content ? self::parse($content, $meta) : false;
    }
    
    public static function curl($url)
    {
        $req = curl_init();
        $opt = [
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_COOKIEJAR      => tempnam(sys_get_temp_dir(), 'curl'),
            CURLOPT_FAILONERROR    => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => ['DNT: 1', 'Accept-Language: en-us,en;q=0.5'],
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:48.0) Gecko/20100101 Firefox/48.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 10,
        ];
        curl_setopt_array($req, $opt);
        if (! $res = curl_exec($req)) return false;
        curl_close($req);
        // mb_convert_encoding is used to avoid encoding issues related to DOMDocument::loadHTML
        // see https://secure.php.net/manual/en/domdocument.loadhtml.php#52251
        return mb_convert_encoding($res, 'HTML-ENTITIES', 'UTF-8');
    }
}
