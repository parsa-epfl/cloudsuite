FROM cloudsuite/debian:base-os

RUN BUILD_DEPS="wget curl ca-certificates" \
    && set -x \
    && apt-get update -y && apt-get install -y --no-install-recommends ${BUILD_DEPS} \
    && rm -rf /var/lib/apt/lists/* 

ENV INDEX_URL http://cloudsuite.ch/download/web_search/index

VOLUME ["/download"]

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT [ "/entrypoint.sh" ]
