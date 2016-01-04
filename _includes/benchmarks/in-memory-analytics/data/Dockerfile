FROM ubuntu:14.04

# Install packages
RUN apt-get update -y && apt-get install -y --no-install-recommends \
      curl \
      unzip \
    && rm -rf /var/lib/apt/lists/*

# Download datasets
RUN mkdir /data \
    && curl -o /data/ml-latest-small.zip http://files.grouplens.org/datasets/movielens/ml-latest-small.zip \
    && unzip -d /data /data/ml-latest-small.zip \
    && rm /data/ml-latest-small.zip \
    && curl -o /data/ml-latest.zip http://files.grouplens.org/datasets/movielens/ml-latest.zip \
    && unzip -d /data /data/ml-latest.zip \
    && rm /data/ml-latest.zip

COPY myratings.csv /data/myratings.csv

VOLUME ["/data"]

