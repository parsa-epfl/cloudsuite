FROM cloudsuite/base-os:ubuntu

# Download datasets

RUN BUILD_DEPS="curl bzip2 ca-certificates" \
    && DATASET_FILENAME="enwiki-latest-pages-articles1.xml-p1p41242.bz2" \
    && set -x \
    && apt-get update -y && apt-get install -y --no-install-recommends ${BUILD_DEPS} \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /data \
    && curl https://dumps.wikimedia.org/enwiki/latest/${DATASET_FILENAME} | bunzip2 > /data/wiki \
    && apt-get purge -y --auto-remove ${BUILD_DEPS}

VOLUME ["/data"]
