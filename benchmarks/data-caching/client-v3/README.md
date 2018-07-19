# Memcached Client #

This `Dockerfile` creates an ubuntu (latest) image representing the Memcached client which tries to access server's data.

Example:

    $ docker pull cloudsuite/data-caching:client
    $ docker run -it --name dc-client --net caching_network cloudsuite/data-caching:client bash
