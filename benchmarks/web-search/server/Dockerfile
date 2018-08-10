FROM cloudsuite/java
LABEL maintainer="Siddharth Gupta <siddharth.gupta@epfl.ch>"

ENV SOLR_USER solr

RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends pbzip2 telnet lsof wget unzip curl \
	&& rm -rf /var/lib/apt/lists/* \
	&& groupadd -r $SOLR_USER  \
	&& useradd -r -g $SOLR_USER $SOLR_USER \
	&& mkdir -p /home/solr \
	&& chown -R $SOLR_USER:$SOLR_USER /home/solr

ENV BASE_PATH /usr/src
ENV SOLR_VERSION 7.4.0
ENV SOLR_HOME $BASE_PATH/solr-$SOLR_VERSION
ENV SOLR_PORT 8983
ENV SOLR_CORE_DIR $BASE_PATH/solr_cores
ENV SERVER_HEAP_SIZE 3g
ENV SOLR_JAVA_HOME $JAVA_HOME
ENV NUM_SERVERS 1
ENV SERVER_0_IP localhost
ENV ZOOKEEPER_PORT $SOLR_PORT

#INSTALL SOLR
RUN 	mkdir -p $BASE_PATH/cloudsuite-web-search \
	&& cd $BASE_PATH \ 
	&& wget -O solr.tar.gz "archive.apache.org/dist/lucene/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz" \
	&& tar -zxf solr.tar.gz 


COPY files/conf $SOLR_HOME/server/solr/configsets/_default/conf

#RELOAD CONFIGURATION
RUN     cd $SOLR_HOME \
	&& mkdir -p $SOLR_CORE_DIR \
	&& cp -R server/solr/* $SOLR_CORE_DIR


RUN 	chown -R $SOLR_USER:$SOLR_USER $BASE_PATH

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE $SOLR_PORT
USER $SOLR_USER

ENTRYPOINT ["/entrypoint.sh"]
