FROM cloudsuite/java:openjdk17

RUN apt-get update -y \
  && apt-get install -y --no-install-recommends procps telnet lsof wget unzip \
  && rm -rf /var/lib/apt/lists/*

ENV BASE_PATH /usr/src
ENV NUTCH_VERSION 1.18
ENV NUTCH_HOME $BASE_PATH/apache-nutch-1.18
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

#INSTALL NUTCH
RUN cd $BASE_PATH \
  && wget --progress=bar:force -O nutch.tar.gz "https://archive.apache.org/dist/nutch/${NUTCH_VERSION}/apache-nutch-${NUTCH_VERSION}-bin.tar.gz" \
  && tar -zxf nutch.tar.gz \
  && rm nutch.tar.gz


#INSTALL SOLR
RUN cd $BASE_PATH \
  && wget --progress=bar:force -O solr.tar.gz "https://archive.apache.org/dist/solr/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz" \
  && tar -zxf solr.tar.gz \
  && rm solr.tar.gz

RUN cd $SOLR_HOME/server/solr/configsets/ \
  && cp -r _default nutch \
  && rm nutch/conf/managed*

COPY files/schema.xml $SOLR_HOME/server/solr/configsets/nutch/conf/.
COPY files/solrconfig.xml $SOLR_HOME/server/solr/configsets/nutch/conf/.
COPY files/nutch-default.xml $NUTCH_HOME/conf/.

RUN cd $NUTCH_HOME \
  && mkdir urls \
  && cd urls 

COPY files/seed.txt $NUTCH_HOME/urls/.

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY generate_index.sh /generate_index.sh
RUN chmod +x /generate_index.sh && ln -s /generate_index.sh /bin/generate_index

EXPOSE $SOLR_PORT

RUN sed -i "/SOLR_JETTY_HOST/c\SOLR_JETTY_HOST=\"0.0.0.0\"" ${SOLR_HOME}/bin/solr.in.sh

ENTRYPOINT ["/entrypoint.sh"]

