FROM ubuntu:14.04
LABEL maintainer="Mark Sutherland <mark.sutherland@epfl.ch>"

ENV memcache_mem_limit 65535
ENV memcache_num_threads 4

RUN apt-get update && apt-get install -y \
	build-essential \
	memcached

CMD bash -c "memcached -u root -m $memcache_mem_limit -t $memcache_num_threads"
