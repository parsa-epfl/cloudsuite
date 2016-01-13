FROM ubuntu

RUN apt-get update && apt-get install wget -y
# create data directory
RUN mkdir /data

RUN cd /data \
  && wget http://parsa.epfl.ch/cloudsuite/software/enwiki-20100904-pages-articles1.xml.bz2


VOLUME ["/data"]
