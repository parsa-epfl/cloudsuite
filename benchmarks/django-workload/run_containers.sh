#!/bin/bash

# This function will wait until the specified port on the specified machine is available
# Parameters: $1 = the IP; $2 = the port; $3 = the name of the service
wait_port() {
  while ! netcat -w 5 "$1" "$2"; do
    echo "Waiting for $3 ..."
    sleep 3
  done
}

docker network create --opt com.docker.network.bridge.name=django --attachable \
       -d bridge --gateway 10.10.10.1 --subnet 10.10.10.0/24                   \
       --ip-range 10.10.10.8/29 django_network

# Start memcached container
echo "Staring memcached container"
docker run -tid -h memcached --name memcached_container --network              \
       django_network --ip 10.10.10.10 memcached-webtier

wait_port 10.10.10.10 11211 memcached
echo "Memcached is up and running!"

# Start cassandra container
echo "Starting cassandra container"
docker run -tid --privileged -h cassandra -e SYSTEM_MEMORY=8 --name cassandra_container           \
           --network django_network --ip 10.10.10.11 cassandra-webtier

wait_port 10.10.10.11 9042 cassandra
echo "Cassandra is up and running!"

# Start graphite container
echo "Starting graphite container"
docker run -tid -h graphite --name graphite_container --network django_network \
           --ip 10.10.10.12 graphite-webtier

wait_port 10.10.10.12 80 graphite
echo "Graphite is up and running!"

# Start uwsgi container
echo "Starting uwsgi container"
docker run -tid -h uwsgi --name uwsgi_container --network django_network        \
           --ip 10.10.10.13 -e GRAPHITE_ENDPOINT=10.10.10.12                    \
           -e CASSANDRA_ENDPOINT=10.10.10.11                                    \
           -e MEMCACHED_ENDPOINT="10.10.10.10:11211"                            \
           -e SIEGE_ENDPOINT=10.10.10.14 uwsgi-webtier

wait_port 10.10.10.13 8000 uwsgi

echo "uWSGI is up and running!"

# Start siege container
echo "Starting siege container"
docker run -ti -h siege --name siege_container --volume=/tmp:/tmp --privileged \
           --network django_network -e ATTEMPTS=10                             \
           -e TARGET_ENDPOINT=10.10.10.13 -e SIEGE_WORKERS=${WORKERS:-185}     \
           siege-webtier
