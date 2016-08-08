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
* Twitter hashtag [#indieweb](https://noapi.subversive.audio/twitter/hashtag/indieweb.json) as JSON
* Twitter search [adama traoré](https://noapi.subversive.audio/twitter/search/adama traoré.html) as HTML
