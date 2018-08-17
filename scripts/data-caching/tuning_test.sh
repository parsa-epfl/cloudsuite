set -e

# Build the images
docker build -t cloudsuite/memcached:1.5.9_ARM -f commons/memcached/1.5.9/Dockerfile_ARM commons/memcached/1.5.9/
docker build -t cloudsuite/data-caching:server_ARM -f benchmarks/data-caching/server/Dockerfile_ARM benchmarks/data-caching/server/
docker build -t cloudsuite/data-caching:client_ARM -f benchmarks/data-caching/client/Dockerfile_ARM benchmarks/data-caching/client/

# Clean up previous runs
docker network create caching_network || true
docker stop dc-server || true
docker stop dc-client || true
docker rm dc-server || true
docker rm dc-client || true

# Run the test
docker run --cpuset-cpus="32-35" --name dc-server --net caching_network -d cloudsuite/data-caching:server_ARM -t 4 -m 2048 -n 550
docker run --cpuset-cpus="48-51" -d --name dc-client --net caching_network cloudsuite/data-caching:client bash -c 'cd /usr/src/memcached/memcached_client/; \
	echo dc-server, 11211 > docker_servers.txt; \
	./loader -a ../twitter_dataset/twitter_dataset_unscaled -o ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -w 4 -S 30 -D 4096 -j -T 1; \
	for i in `seq 10000 500 155000`; do \
		echo rps: $i; \
		./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -g 0.8 -T 5 -c 200 -w 4 -e -r $i & \
		sleep 355; \
		for j in  {1..2}j; do \
			mpstat -P ALL 25 1; \
		done; \
		pkill loader; \
	done'

# Now you can have the logs by doing a `docker logs dc-client` and then process them with the python script plot.py
