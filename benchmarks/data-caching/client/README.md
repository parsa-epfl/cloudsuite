# Memcached Client #

This `Dockerfile` creates an ubuntu (latest) image representing the Memcached client which tries to access server's data.

Example:

    $ docker pull cloudsuite/datacaching:client
    $ docker run -it --name dc-client --link=dc-server cloudsuite/datacaching:client bash
