# noapi-api
[NoAPI](https://github.com/alct/noapi) API endpoint.

Highly experimental.

# Usage

Make your query in the form of `https://DOMAIN/BACKEND/ACTION/QUERY.EXT` where:

* DOMAIN is the domain/subdomain where you have setup noapi-api
* BACKEND is the silo you're querying (only twitter supported at the moment)
* ACTION is user or search or tag
* EXT is either json or html

# Examples

* Twitter user [@alct](https://noapi.subversive.audio/twitter/user/alct.html) as HTML page
* Twitter hashtag [#indieweb](https://noapi.subversive.audio/twitter/tag/indieweb.json) as JSON
* Twitter search [adama traoré](https://noapi.subversive.audio/twitter/search/adama traoré.html) as HTML

# Caching

Some file caching is provided by `classes/Cache.php`. This allows requests to be processed in ~5ms, while initial requests will take ~1s in my case.

If you're intending serious production use, you should probably consider swapping this file cache for an in-memory cache like Redis.

By default, requests are cached for 5 minutes (300 seconds).
