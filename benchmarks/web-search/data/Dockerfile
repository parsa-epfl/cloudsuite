FROM ubuntu:14.04
MAINTAINER Javier Picorel <javier.picorel@epfl.ch>

RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends wget tar \
	&& rm -rf /var/lib/apt/lists/*

ENV PACKAGES_URL http://lsi-www.epfl.ch/parsa
ENV INDEX_URL $PACKAGES_URL/index


RUN mkdir /data \
	&& cd /data \
	&& wget -q -O - $INDEX_URL \
	| tar zxvf - -C .

VOLUME ["/data"]
