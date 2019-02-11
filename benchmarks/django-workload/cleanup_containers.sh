#!/bin/bash

sudo docker stop memcached_container
sudo docker rm memcached_container

sudo docker stop cassandra_container
sudo docker rm cassandra_container

sudo docker stop graphite_container
sudo docker rm graphite_container

sudo docker stop uwsgi_container
sudo docker rm uwsgi_container

sudo docker stop siege_container
sudo docker rm siege_container

sudo docker network rm django_network
