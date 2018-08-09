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

docker run --cpuset-cpus="0-3" --name dc-server1 --net caching_network -d cloudsuite/data-caching:server
#docker run --cpuset-cpus="4-7" --name dc-server2 --net caching_network -d cloudsuite/data-caching:server

docker run --cpuset-cpus="4-7" -d --name dc-client --net caching_network cloudsuite/data-caching:client bash -c 'cd /usr/src/memcached/memcached_client/; \
echo dc-server1, 11211 > docker_servers.txt; \
echo dc-server1, 11211 > docker_servers.txt; \
./loader -a ../twitter_dataset/twitter_dataset_unscaled -o ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -w 4 -S 30 -D 4096 -j -T 1; \
for i in `seq 10000 500 155000`; do \
	echo rps: $i; \
	./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -g 0.8 -T 50 -c 200 -w 4 -e -r $i & \
	sleep 300; \
	for j in  {1..2}j; do \
		mpstat -P ALL 25 1; \
	done; \
	pkill loader; \
done'


#while docker ps | grep 'dc-client'; do
#	echo Still waiting for scale and warmup...
#	sleep 10
#done

#./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -g 0.8 -T 1 -c 200 -w 8" > output.txt
