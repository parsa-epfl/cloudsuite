FROM cloudsuite/java:openjdk8

USER root
ENV SOLR_USER solr
RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends telnet lsof wget unzip emacs git sudo bzip2 curl \
 	&& rm -rf /var/lib/apt/lists/* \ 
	&& groupadd -r $SOLR_USER  \
 	&& useradd -r -g $SOLR_USER $SOLR_USER

ENV BASE_PATH /home/solr/
ENV SOLR_VERSION 7.4.0
ENV SOLR_HOME $BASE_PATH/
ENV SOLR_PORT 8983
RUN mkdir -p /home/solr

RUN mkdir -p $BASE_PATH/cloudsuite-web-search \
     && cd $BASE_PATH \ 
     && wget -O solr.tar.gz "archive.apache.org/dist/lucene/solr/$SOLR_VERSION/solr-$SOLR_VERSION.tgz" \
     && tar -zxf solr.tar.gz 




ADD cloudsuite_web_search /home/solr/solr-7.4.0/example/example-DIH/solr/cloudsuite_web_search/

RUN chown -R solr /home/solr 
EXPOSE $SOLR_PORT
COPY docker_entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
USER $SOLR_USER
WORKDIR /home/solr
ENTRYPOINT ["/entrypoint.sh"]
	
