set -e


cd commons/java/openjdk8

docker build -t cloudsuite/java:openjdk8 -f Dockerfile .
docker build -t cloudsuite/java:latest -f Dockerfile .


cd ../openjdk7

docker build -t cloudsuite/java:openjdk7 -f Dockerfile .

cd ../../..

./test_build.sh

docker network create caching_network || true

docker stop $(docker ps -q) || true
docker rm dc-server1 || true
docker rm dc-server2 || true
docker rm dc-server3 || true
docker rm dc-server4 || true

docker rm dc-client || true

docker run --cpuset-cpus="0-15" --name dc-server1 --net caching_network -d cloudsuite/data-caching:server
docker run --cpuset-cpus="16-31" --name dc-server2 --net caching_network -d cloudsuite/data-caching:server

docker run -it --name dc-client --net caching_network cloudsuite/data-caching:client bash -c "cd /usr/src/memcached/memcached_client/; \
echo dc-server1, 11211 > docker_servers.txt; \
echo dc-server2, 11211 >> docker_servers.txt; \
./loader -a ../twitter_dataset/twitter_dataset_unscaled -o ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -w 4 -S 30 -D 4096 -j -T 1;" > output.txt


#./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -g 0.8 -T 1 -c 200 -w 8" > output.txt
