FROM cloudsuite/graphite-statsd

ENV DEBIAN_FRONTEND noninteractive

COPY storage-schemas.conf /opt/graphite/conf/storage-schemas.conf
COPY blacklist.conf /opt/graphite/conf/blacklist.conf
COPY carbon.conf /opt/graphite/conf/carbon.conf

ENV DEBIAN_FRONTEND teletype
