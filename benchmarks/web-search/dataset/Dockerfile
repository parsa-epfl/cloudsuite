FROM cloudsuite/base-os:ubuntu

RUN BUILD_DEPS="wget curl ca-certificates" \
    && set -x \
    && apt-get update -y && apt-get install -y --no-install-recommends ${BUILD_DEPS} \
    && rm -rf /var/lib/apt/lists/* 

ENV INDEX_URL http://datasets.epfl.ch/cloudsuite/index_14GB.tar.gz

VOLUME ["/download"]

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT [ "/entrypoint.sh" ]
