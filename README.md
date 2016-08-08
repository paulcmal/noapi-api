# noapi-api
[NoAPI](https://github.com/alct/noapi) API endpoint.

Highly experimental.

Allows you to query the API like :
https://DOMAIN/BACKEND/ACTION/QUERY.EXT where:

* DOMAIN is the domain/subdomain where you have setup noapi-api
* BACKEND is the silo you're querying (only twitter supported at the moment)
* ACTION is user or search or tag
* EXT is either json or html

For example :

* https://noapi.subversive.audio/twitter/user/alct.html
* https://noapi.subversive.audio/twitter/tag/adama.json
