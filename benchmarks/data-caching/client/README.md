# Memcached Client #

This `Dockerfile` creates a debian image representing the Memcached client which tries to access server's data.

Example:

    $ docker pull cloudsuite/data-caching:client
    $ docker run -it --name dc-client --net host cloudsuite/data-caching:client bash
