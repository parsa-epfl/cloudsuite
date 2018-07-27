#!/bin/bash

echo "" > graph.txt

for i in `seq 1000 26000 201000`; do
	echo rps: $i >> graph.txt
    
    ./test_data_caching.sh
	docker start dc-client
	docker exec -i dc-client bash -c "cd /usr/src/memcached/memcached_client/; \
echo dc-server1, 11211 > docker_servers.txt; \
echo dc-server2, 11211 >> docker_servers.txt; \
./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -g 0.8 -T 1 -c 200 -w 8 -e -r  $i" >> graph.txt &

	echo started benchmark

	for j in {1..11}; do
		sleep 20
		mpstat -P ALL >> graph.txt
	done
done
