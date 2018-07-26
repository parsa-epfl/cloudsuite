set -e

docker network rm test_network || true 

docker container rm -f mysql_server memcache_server webserver faban_client || true

docker system prune -f || true

docker network create test_network || true


cd ~/Documents/cloudsuite/benchmarks/web-serving

cd db_server

docker build -t db_server -f Dockerfile .

cd ../memcached_server

docker build -t memcached_server -f Dockerfile .

cd ../web_server

docker build -t web_server -f Dockerfile .

cd ../faban_client

docker build -t faban_client -f Dockerfile .

#cd ../../../commons/java/openjdk8

#docker build -t cloudsuite/java:openjdk8 -f Dockerfile .
#docker build -t cloudsuite/java:latest -f Dockerfile .


#cd ../openjdk7

#docker build -t cloudsuite/java:openjdk7 -f Dockerfile .
#docker build -t cloudsuite/java:latest -f Dockerfile .

docker run -dt --network=test_network  --name=mysql_server db_server webserver

docker run -dt --network=test_network --name=memcache_server memcached_server

docker run -dt  -v /tmp/test.pcap:/tmp/test.pcap --network=test_network  --name=webserver web_server /etc/bootstrap.sh
#server_ip=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' webserver)

echo "Server IP is $server_ip"

#docker run --net=test_network --name=faban_client faban_client $server_ip
docker run --net=test_network --name=faban_client faban_client webserver


