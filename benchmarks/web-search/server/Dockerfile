FROM cloudsuite/java
LABEL maintainer="Siddharth Gupta <siddharth.gupta@epfl.ch>"

ENV SOLR_USER solr

RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends telnet lsof wget unzip \
	&& rm -rf /var/lib/apt/lists/* \
	&& groupadd -r $SOLR_USER  \
	&& useradd -r -g $SOLR_USER $SOLR_USER

ENV BASE_PATH /usr/src
ENV SOLR_VERSION 5.2.1
ENV SOLR_HOME $BASE_PATH/solr-$SOLR_VERSION
ENV PACKAGES_URL http://cloudsuite.ch/download/web_search
ENV INDEX_URL $PACKAGES_URL/index
ENV SCHEMA_URL $PACKAGES_URL/schema.xml
ENV SOLR_CONFIG_URL $PACKAGES_URL/solrconfig.xml
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

RUN 	cd $SOLR_HOME/server/solr/configsets/basic_configs/conf \
	&& wget $SCHEMA_URL -O schema.xml \
	&& wget $SOLR_CONFIG_URL -O solrconfig.xml

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
