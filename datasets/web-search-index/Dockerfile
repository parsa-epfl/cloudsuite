FROM ubuntu:18.04
ENV INDEX_PATH /home/solr/index_data
ENV SERVER_PATH parsapc20.epfl.ch:/cloudsuite/web_search_index
ENV INDEX_NAME index_13g
ENV INDEX_URL $SERVER_PATH/$INDEX_NAME.tar.gz
RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends wget tar \
	&& rm -rf /var/lib/apt/lists/* \
	&& mkdir -p $INDEX_PATH

COPY entrypoint.sh /entrypoint.sh
RUN chmod u+x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
VOLUME ["/home/solr/index_data"]


