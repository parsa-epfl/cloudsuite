FROM cloudsuite/java:openjdk17

RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends procps telnet lsof wget unzip \
	&& rm -rf /var/lib/apt/lists/*

COPY files/limits.txt /root/.
RUN cat /root/limits.txt >> /etc/security/limits.conf

ENV BASE_PATH /usr/src
ENV SOLR_VERSION 9.1.1
ENV SOLR_HOME $BASE_PATH/solr-$SOLR_VERSION
ENV PACKAGES_URL http://datasets.epfl.ch/cloudsuite
ENV INDEX_URL $PACKAGES_URL/index
ENV SCHEMA_URL $PACKAGES_URL/schema.xml
ENV SOLR_CONFIG_URL $PACKAGES_URL/solrconfig.xml
ENV SOLR_PORT 8983
ENV SOLR_CORE_DIR $BASE_PATH/solr_cores
ENV SERVER_HEAP_SIZE 3g
ENV NUM_SERVERS 1
ENV SERVER_0_IP localhost
ENV ZOOKEEPER_PORT $SOLR_PORT

#INSTALL SOLR
RUN cd $BASE_PATH \ 
  && wget --progress=bar:force -O solr.tar.gz "https://archive.apache.org/dist/solr/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz" \ 
  && tar -zxf solr.tar.gz \
  && rm solr.tar.gz


RUN cd $SOLR_HOME/server/solr/configsets/ \
  && cp -r _default cloudsuite_web_search \
  && rm cloudsuite_web_search/conf/managed* 

COPY files/schema.xml $SOLR_HOME/server/solr/configsets/cloudsuite_web_search/conf/.
COPY files/solrconfig.xml $SOLR_HOME/server/solr/configsets/cloudsuite_web_search/conf/.

#RELOAD CONFIGURATION
RUN     cd $SOLR_HOME \
	&& mkdir -p $SOLR_CORE_DIR \
	&& cp -R server/solr/* $SOLR_CORE_DIR 

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE $SOLR_PORT

RUN sed -i "/SOLR_JETTY_HOST/c\SOLR_JETTY_HOST=\"0.0.0.0\"" ${SOLR_HOME}/bin/solr.in.sh 

ENTRYPOINT ["/entrypoint.sh"]
