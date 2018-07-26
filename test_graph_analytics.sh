#! /bin/bash

set -e


cd commons/java/openjdk8

docker build -t cloudsuite/java:openjdk8 -f Dockerfile .
docker build -t cloudsuite/java:latest -f Dockerfile .


cd ../openjdk7

docker build -t cloudsuite/java:openjdk7 -f Dockerfile .

cd ../../..


docker build -t cloudsuite/spark:latest commons/spark/2.3.1
docker build -t cloudsuite/spark:2.3.1 commons/spark/2.3.1
docker build -t cloudsuite/spark:2.1.0 commons/spark/2.1.0
docker build -t cloudsuite/graph-analytics:latest benchmarks/graph-analytics/3.0

docker network create spark-net || true

docker stop $(docker ps -q) || true
docker rm $(docker ps -aq) || true

docker create --name data cloudsuite/twitter-dataset-graph

docker run -dP --net spark-net --hostname spark-master --name spark-master \
             cloudsuite/spark master
docker run -dP --net spark-net --volumes-from data --name spark-worker-01 \
             cloudsuite/spark worker spark://spark-master:7077

docker run --rm --net spark-net --volumes-from data \
             cloudsuite/graph-analytics \
             --driver-memory 1g --executor-memory 4g \
             --master spark://spark-master:7077
