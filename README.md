# Flickr-PHP-Oauth

I wanted to get access tokens to query my own account via the Flickr API - but with the old authentication system no longer available, there's no choice but oAuth.

This script handles two requests to Flickr:

https://www.flickr.com/services/oauth/request_token

and

https://www.flickr.com/services/oauth/access_token

.. dealing with all the required signing, and returns an array with your user access token and secret. 

It's designed to run on Heroku, and requires some environment variables:

* FL_API - your Flickr API key
* FL_SEC - your Flickr API secret
* APP_URL - the URL it is running at (eg, https://something.herokuapp.com/)

After the first stage of the process, you will be shown an oAuth Secret Key - set it as another environment variable called OTS so it's accessible in the next step. Obviously in a proper solution this would be stored in a DB.

For more info on the request signing, I recommend checking this out: http://www.wackylabs.net/2011/12/oauth-and-flickr-part-2/

Hope it's helpful.

Tom
