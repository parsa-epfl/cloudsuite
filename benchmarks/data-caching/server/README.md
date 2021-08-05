# Memcached Server #

This `Dockerfile` creates a debian image containing the latest version of Memcached (1.6.6).
Memcached will be started as a daemon with the passed parameters.
Example:

    $ docker pull cloudsuite/data-caching:server
    $ docker run --name dc-server --net host -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550
