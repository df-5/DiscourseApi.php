# DiscourseApi.php

This is really more of a learning experience -- working on a WordPress site for a friend and needed extensible hooks for Discourse.

I've only personally tested this on HHVM (5.6.99-hhvm) and PHP 5.6.8+.

Pull requests and feature additions are welcome, and this will be maintained for a decent amount of time as I've really taken a liking to Discourse and have been using it in a few projects.

At some point I'm going to namespace everything and separate things by user, group, sso, etc, but this will be on another branch/version to maintain backward compatibility.

## Troubleshooting

### End user IPs are that of the server (post content, registration, etc.)
Enable realip/x-f-f on whatever is backing your instance. 
Check samples/ to persist this if you are directly running nginx from docker.

#### nginx
`````
set_real_ip_from  192.168.5.150;
real_ip_header    X-Forwarded-For;
`````

#### apache
`````
RemoteIPHeader X-Forwarded-For
RemoteIPInternalProxy 192.168.5.150
`````

### 403/404/Not allowed/etc
Your username must match your API key (generated from User's Profile -> Admin -> Generate Key).
It's likely not possible to generate a key for the `system` account; it just throws a CSRF token error.

You'll probably want to make another (possibly admin) user account and generate a key, or if you're just going to post threads or replies, a normal account will do. 
The API is subject to the same rate limits as normal users, so you may want to manually set a trust level so they're allowed to post links or post more quickly if necessary.

### Guzzle is throwing HTTPS exceptions
You probably don't have a cert/CA/bundle for whatever certificate you are using.

