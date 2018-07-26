#! /bin/bash

set -e


cd commons/java/openjdk8

docker build -t cloudsuite/java:openjdk8 -f Dockerfile .
docker build -t cloudsuite/java:latest -f Dockerfile .


cd ../openjdk7

docker build -t cloudsuite/java:openjdk7 -f Dockerfile .

cd ../../..

docker build -t cloudsuite/media-streaming:client benchmarks/media-streaming/client
docker build -t cloudsuite/media-streaming:server benchmarks/media-streaming/server
docker build -t cloudsuite/media-streaming:dataset benchmarks/media-streaming/dataset

docker network create streaming_network || true

docker stop $(docker ps -q) || true
docker rm $(docker ps -aq) || true

docker create --name streaming_dataset cloudsuite/media-streaming:dataset

docker run -d --name streaming_server --volumes-from streaming_dataset --net streaming_network cloudsuite/media-streaming:server
docker run -t --name=streaming_client -v /home/aasgari/Documents/temp/streaming-output:/output --volumes-from streaming_dataset --net streaming_network cloudsuite/media-streaming:client streaming_server

