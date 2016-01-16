# Memcached Server #

This `Dockerfile` creates an ubuntu (latest) image containing the latest version of Memcached (1.4.24).
Memcached will be started as a daemon with the passed parameters.
Example:

    $ docker pull cloudsuite/data-caching:server
    $ docker run --name dc-server --net caching_network -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550
