#! /bin/bash

set -e 


cd commons/java/openjdk8

docker build -t cloudsuite/java:openjdk8 -f Dockerfile .
docker build -t cloudsuite/java:latest -f Dockerfile .


cd ../openjdk7

docker build -t cloudsuite/java:openjdk7 -f Dockerfile .

cd ../../..

docker stop $(docker ps -q) || true
docker rm $(docker ps -qa) || true

docker build -t cloudsuite/data-serving:server benchmarks/data-serving/server
docker build -t cloudsuite/data-serving:client benchmarks/data-serving/client

docker network create serving_network || true 


docker stop cassandra-server-seed || true
docker rm cassandra-server-seed || true
docker run -d --name cassandra-server-seed --net serving_network cloudsuite/data-serving:server


servers="cassandra-server-seed"

for i in {1..2}; do
	docker stop cassandra-server$i || true
	docker rm cassandra-server$i || true
	docker run -d --name "cassandra-server$i" --net serving_network -e CASSANDRA_SEEDS=cassandra-server-seed cloudsuite/data-serving:server
	servers="$servers,cassandra-server$i"
	sleep 60
done


docker stop cassandra-client || true
docker rm cassandra-client || true
docker run -e RECORDCOUNT=10000 -e OPERATIONCOUNT=10000 --name cassandra-client --net serving_network cloudsuite/data-serving:client "$servers"

