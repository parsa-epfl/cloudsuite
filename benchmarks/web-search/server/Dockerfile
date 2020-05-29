FROM cloudsuite/java:adoptopenjdk8
LABEL maintainer="Siddharth Gupta <siddharth.gupta@epfl.ch>"

RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends procps telnet lsof wget unzip \
	&& rm -rf /var/lib/apt/lists/*

ENV BASE_PATH /usr/src
ENV SOLR_VERSION 6.6.6
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
	&& wget --progress=bar:force -O solr.tar.gz "archive.apache.org/dist/lucene/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz" \
	&& tar -zxf solr.tar.gz 

RUN 	cd $SOLR_HOME/server/solr/configsets/basic_configs/conf \
	&& wget --progress=bar:force $SCHEMA_URL -O schema.xml \
	&& wget --progress=bar:force $SOLR_CONFIG_URL -O solrconfig.xml

#RELOAD CONFIGURATION
RUN     cd $SOLR_HOME \
	&& mkdir -p $SOLR_CORE_DIR \
	&& cp -R server/solr/* $SOLR_CORE_DIR 

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE $SOLR_PORT

ENTRYPOINT ["/entrypoint.sh"]
