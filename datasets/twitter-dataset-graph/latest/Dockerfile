FROM cloudsuite/base-os:ubuntu
#socialcomputing.asu.edu/uploads/1296759055/Twitter-dataset.zip
# Install packages
RUN BUILD_DEPS="wget unzip" \
    && apt-get update && apt-get install -y --no-install-recommends ${BUILD_DEPS} \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /data \
    && wget --no-check-certificate -O /data/Twitter-dataset.zip http://datasets.epfl.ch/cloudsuite/twitter_dataset.zip \
    && unzip -d /data /data/Twitter-dataset.zip \
    && rm /data/Twitter-dataset.zip \
    && apt-get purge -y --auto-remove ${BUILD_DEPS}

VOLUME ["/data"]
