FROM cloudsuitetest/faban:1.4

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

RUN cd $BASE_PATH \
	&& wget "archive.apache.org/dist/ant/binaries/apache-ant-$ANT_VERSION-bin.tar.gz" \
	&& tar -zxf apache-ant-$ANT_VERSION-bin.tar.gz \
	&& ln -s $BASE_PATH/apache-ant-$ANT_VERSION/ $ANT_HOME

COPY files $FABAN_HOME/.

RUN cd $FABAN_HOME/search \
	&& sed -i "/faban.home/c\\faban.home=$FABAN_HOME" build.properties \
	&& sed -i "/ant.home/c\\ant.home=$ANT_HOME" build.properties \
	&& sed -i "/faban.url/c\\faban.url=http://localhost:$FABAN_PORT/" build.properties

COPY docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE $FABAN_PORT

ENTRYPOINT ["/entrypoint.sh"]
