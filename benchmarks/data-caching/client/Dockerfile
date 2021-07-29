FROM cloudsuitetest/debian:base-os
LABEL maintainer="Arash Pourhabibi Zarandi <arash.pourhabibi@epfl.ch>"

# add our user and group first to make sure their IDs get assigned consistently, regardless of whatever dependencies get added
RUN groupadd -r memcache && useradd -r -g memcache memcache

ENV DEBIAN_FRONTEND noninteractive

# libevent-dev will include libevent, so it's not necessary to install it standalone
# RUN apt-get update && apt-get install -y libevent-2.1-6 vim --no-install-recommends \
	# && rm -rf /var/lib/apt/lists/*

RUN buildDeps='curl gcc libc6-dev make' \	
	&& set -x \
	&& apt-get update && apt-get install -y $buildDeps libevent-dev vim --no-install-recommends \
	&& rm -rf /var/lib/apt/lists/* \
	&& curl -k -L --remote-name http://github.com/parsa-epfl/memcached-loadtester/archive/refs/tags/v4.0.tar.gz \
	&& mkdir -p /usr/src/memcached \
	&& tar xvf v4.0.tar.gz -C /usr/src/memcached --strip-components=1 \
	&& rm v4.0.tar.gz \
	&& cd /usr/src/memcached/memcached_client \
	&& make -j $(nproc) \
	&& chown -R memcache:memcache /usr/src/memcached \
	&& apt-get purge -y --auto-remove $buildDeps

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

USER memcache
CMD ["-rps", "18000"]
