FROM ubuntu:18.04
LABEL maintainer="Arash Pourhabibi Zarandi <arash.pourhabibi@epfl.ch>"

# This is based on the official memcached Dockerfile

# add our user and group first to make sure their IDs get assigned consistently, regardless of whatever dependencies get added
RUN groupadd -r memcache && useradd -r -g memcache memcache

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
		libevent-2.1-6 \
	&& rm -rf /var/lib/apt/lists/*

ENV MEMCACHED_VERSION 1.5.10

# this checksum was not provided with the author of memcached, it is checksum of the working downloaded version
ENV MEMCACHED_SHA1 fff9351b360a09497cd805d3e4c1ffbe860d067d

RUN buildDeps='curl gcc libc6-dev libevent-dev make perl' \
	&& set -x \
	&& apt-get update && apt-get install -y $buildDeps --no-install-recommends \
	&& rm -rf /var/lib/apt/lists/* \
	&& curl -SL "http://memcached.org/files/memcached-$MEMCACHED_VERSION.tar.gz" -o memcached.tar.gz \
	&& echo "$MEMCACHED_SHA1 memcached.tar.gz" | sha1sum -c - \
	&& mkdir -p /usr/src/memcached \
	&& tar -xzf memcached.tar.gz -C /usr/src/memcached --strip-components=1 \
	&& rm memcached.tar.gz \
	&& cd /usr/src/memcached \
	&& ./configure \
	&& make \
	&& make install \
	&& cd / && rm -rf /usr/src/memcached \
	&& apt-get purge -y --auto-remove $buildDeps

ENTRYPOINT ["memcached"]

USER memcache
EXPOSE 11211
