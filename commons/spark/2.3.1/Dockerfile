FROM cloudsuite/java:openjdk8

# Install dependencies
RUN apt-get update -y && apt-get install -y --no-install-recommends \
      libblas3 \
      libgfortran3 \
      liblapack3 \
      curl \
    && rm -rf /var/lib/apt/lists/*

ENV SPARK_VERSION 2.3.1
ENV HADOOP_VERSION 2.7
ENV MIRROR  https://archive.apache.org/dist/spark/
ENV SPARK_HOME /opt/spark-${SPARK_VERSION}

# Install Spark
RUN set -x \
    && curl ${MIRROR}spark-${SPARK_VERSION}/spark-${SPARK_VERSION}.tgz | tar -xzC /root \
    && cd /root/spark-${SPARK_VERSION} \
    && ./dev/make-distribution.sh -Phadoop-${HADOOP_VERSION} -Pyarn -Pnetlib-lgpl >/dev/null \
    && mv dist /opt/spark-${SPARK_VERSION} \
    && cd /root && rm -r .zinc .m2 spark-${SPARK_VERSION} \
    && apt-get purge -y --auto-remove ${BUILD_DEPS}

COPY files /root/

# Expose Spark ports
ENV SPARK_MASTER_PORT 7077
ENV SPARK_WEBUI_PORT 8080
EXPOSE ${SPARK_MASTER_PORT} ${SPARK_WEBUI_PORT}

ENTRYPOINT ["/root/entrypoint.sh"]
