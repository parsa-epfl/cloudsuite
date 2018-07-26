#! /bin/bash

set -e



cd commons/java/openjdk8

docker build -t cloudsuite/java:openjdk8 -f Dockerfile .
docker build -t cloudsuite/java:latest -f Dockerfile .


cd ../openjdk7

docker build -t cloudsuite/java:openjdk7 -f Dockerfile .

cd ../../..

docker pull cloudsuite/web-search:client
docker build -t cloudsuite/web-search:client benchmarks/web-search/client
docker build -t cloudsuite/web-search:server benchmarks/web-search/server

docker network create search_network || true

docker stop $(docker ps -q)
docker rm $(docker ps -aq)

docker run -d --name server --net search_network -p 8983:8983 cloudsuite/web-search:server 12g 1

while true; do
	if docker logs --tail 100 server | grep -q 'Index Node IP Address: 172'; then
		break;
	fi
	echo "Index is not ready ... "
	sleep 30
done

server_ip=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' server)

docker run -it --name client --net search_network cloudsuite/web-search:client $server_ip 50 90 60 60

