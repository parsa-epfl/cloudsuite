FROM cloudsuite/faban:1.4

ENV FABAN_PORT 9980
ENV ANT_VERSION 1.9.6
ENV ANT_HOME $BASE_PATH/ant

ENV CLIENT_HEAP_SIZE 2g
ENV DRIVER_DIR $FABAN_HOME/search
ENV POLICY_PATH $DRIVER_DIR/config/security/driver.policy
ENV BENCHMARK_CONFIG $FABAN_HOME/search/deploy/run.xml

ENV SOLR_PORT 8983

ENV RAMP_UP 90
ENV RAMP_DOWN 60
ENV STEADY_STATE 60
ENV SCALE 50
ENV AGENT_ID 1
ENV NUM_AGENTS 1

# The entrypoint is a Python script
RUN apt update && apt install python3 -y

RUN cd $BASE_PATH \
	&& wget "archive.apache.org/dist/ant/binaries/apache-ant-$ANT_VERSION-bin.tar.gz" \
	&& tar -zxf apache-ant-$ANT_VERSION-bin.tar.gz \
	&& ln -s $BASE_PATH/apache-ant-$ANT_VERSION/ $ANT_HOME

COPY files $FABAN_HOME/.

RUN cd $FABAN_HOME/search \
	&& sed -i "/faban.home/c\\faban.home=$FABAN_HOME" build.properties \
	&& sed -i "/ant.home/c\\ant.home=$ANT_HOME" build.properties \
	&& sed -i "/faban.url/c\\faban.url=http://localhost:$FABAN_PORT/" build.properties \
	&& mkdir -p $FABAN_HOME/search/deploy


#COPY docker-entrypoint.sh /entrypoint.sh
COPY docker-entrypoint.py /docker-entrypoint.py
RUN chmod +x /docker-entrypoint.py

EXPOSE $FABAN_PORT

ENTRYPOINT ["/docker-entrypoint.py"]
