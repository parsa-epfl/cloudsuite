FROM ubuntu:14.04

# Install packages
RUN apt-get update && apt-get install -y --no-install-recommends \
      wget \
      unzip \
    && rm -rf /var/lib/apt/lists/*

# Download datasets
RUN mkdir /data \
    && cd /data \
    && wget socialcomputing.asu.edu/uploads/1296759055/Twitter-dataset.zip \
    && unzip Twitter-dataset.zip \
    && rm /data/Twitter-dataset.zip \
    && sed -i "s/,/ /g" /data/Twitter-dataset/data/edges.csv \
    && mv /data/Twitter-dataset/data/edges.csv /data/edges.csv \
    && rm -rf /data/Twitter-dataset

VOLUME ["/data"]