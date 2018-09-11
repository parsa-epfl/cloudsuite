#!/bin/bash

docker stop memcached_container
docker rm memcached_container

docker stop cassandra_container
docker rm cassandra_container

docker stop graphite_container
docker rm graphite_container

docker stop uwsgi_container
docker rm uwsgi_container

docker stop siege_container
docker rm siege_container

docker network rm django_network
