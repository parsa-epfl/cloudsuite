FROM ubuntu:14.04

# Install dependencies
RUN apt-get update -y && apt-get install -y --no-install-recommends \
      libblas3 \
      libgfortran3 \
      liblapack3 \
      openjdk-8-jre-headless \
    && rm -rf /var/lib/apt/lists/*

ENV SPARK_VERSION 2.1.0
ENV HADOOP_VERSION 2.7
ENV SPARK_HOME /opt/spark-${SPARK_VERSION}

# Install Spark
RUN BUILD_DEPS="curl openjdk-8-jdk" \
    && set -x \
    && apt-get update -y && apt-get install -y --no-install-recommends ${BUILD_DEPS} \
    && rm -rf /var/lib/apt/lists/* \
    && MIRROR=$(curl -s http://www.apache.org/dyn/closer.lua?as_json=1 | grep preferred | awk '{print $2}' | sed -e 's/"//g') \
    && SPARK_PATH=spark/spark-${SPARK_VERSION}/spark-${SPARK_VERSION}.tgz \
    && curl ${MIRROR}${SPARK_PATH} | tar -xzC /root \
    && export JAVA_HOME=$(readlink -f /usr/bin/javac | sed "s:/bin/javac::") \
    && cd /root/spark-${SPARK_VERSION} \
    && ./make-distribution.sh -Phadoop-${HADOOP_VERSION} -Pyarn -Pnetlib-lgpl >/dev/null \
    && mv dist /opt/spark-${SPARK_VERSION} \
    && cd /root && rm -r .zinc .m2 spark-${SPARK_VERSION} \
    && apt-get purge -y --auto-remove ${BUILD_DEPS}

COPY files /root/

# Expose Spark ports
ENV SPARK_MASTER_PORT 7077
ENV SPARK_WEBUI_PORT 8080
EXPOSE ${SPARK_MASTER_PORT} ${SPARK_WEBUI_PORT}

ENTRYPOINT ["/root/entrypoint.sh"]
