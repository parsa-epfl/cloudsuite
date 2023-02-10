FROM cloudsuite/base-os:ubuntu

# Download datasets
RUN BUILD_DEPS="curl unzip" \
    && set -x \
    && apt-get update -y && apt-get install -y --no-install-recommends ${BUILD_DEPS} \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /data \
    && curl -o /data/ml-latest-small.zip http://files.grouplens.org/datasets/movielens/ml-latest-small.zip \
    && unzip -d /data /data/ml-latest-small.zip \
    && rm /data/ml-latest-small.zip \
    && curl -o /data/ml-latest.zip http://files.grouplens.org/datasets/movielens/ml-latest.zip \
    && unzip -d /data /data/ml-latest.zip \
    && rm /data/ml-latest.zip \
    && apt-get purge -y --auto-remove ${BUILD_DEPS}

COPY files /data/

VOLUME ["/data"]
