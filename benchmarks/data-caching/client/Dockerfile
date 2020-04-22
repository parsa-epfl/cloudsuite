FROM cloudsuite/debian:base-os
LABEL maintainer="Arash Pourhabibi Zarandi <arash.pourhabibi@epfl.ch>"

# add our user and group first to make sure their IDs get assigned consistently, regardless of whatever dependencies get added
RUN groupadd -r memcache && useradd -r -g memcache memcache

ENV DEBIAN_FRONTEND noninteractive

COPY memcached.tar.gz /memcached.tar.gz

RUN apt-get update && apt-get install -y libevent-2.1-6 vim --no-install-recommends \
	&& rm -rf /var/lib/apt/lists/*

RUN buildDeps='curl gcc libc6-dev libevent-dev make' \
	&& set -x \
	&& apt-get update && apt-get install -y $buildDeps --no-install-recommends \
	&& rm -rf /var/lib/apt/lists/* \
	&& mkdir -p /usr/src/memcached \
	&& tar -xzf memcached.tar.gz -C /usr/src/memcached --strip-components=1 \
	&& rm memcached.tar.gz \
	&& cd /usr/src/memcached/memcached_client \
	&& make \
	&& chown -R memcache:memcache /usr/src/memcached \
	&& apt-get purge -y --auto-remove $buildDeps

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

USER memcache
CMD ["-rps", "18000"]
