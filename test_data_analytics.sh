#!/bin/bash

set -e 

docker stop $(docker ps -q) || true
docker rm $(docker ps -aq) || true

cd commons/java/openjdk8

docker build -t cloudsuite/java:openjdk8 -f Dockerfile .
docker build -t cloudsuite/java:latest -f Dockerfile .


cd ../openjdk7

docker build -t cloudsuite/java:openjdk7 -f Dockerfile .

cd ../../../

echo `pwd`
echo `ls`
echo `ls benchmarks`

#docker build -t cloudsuite/hadoop:latest commons/hadoop/2.7.6
docker build -t cloudsuite/hadoop:latest commons/hadoop/2.9.1
docker build -t cloudsuite/hadoop:2.9.1 commons/hadoop/2.9.1
docker build -t cloudsuite/data-analytics benchmarks/data-analytics/3.0.0

docker network create hadoop-net || true 

docker stop master || true
docker rm master || true
docker run -d --net hadoop-net --name master --hostname master cloudsuite/data-analytics master

for i in {1..2}; do
	docker stop slave$i || true
	docker rm slave$i || true
	docker run -d --net hadoop-net --name slave$i --hostname slave$i cloudsuite/hadoop slave
done

docker exec master benchmark
