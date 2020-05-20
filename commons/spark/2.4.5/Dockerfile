FROM cloudsuite/java:openjdk11

ENV SPARK_VERSION 2.4.5
ENV HADOOP_VERSION 2.7
ENV MIRROR  https://archive.apache.org/dist/spark/
ENV SPARK_HOME /opt/spark-${SPARK_VERSION}

# Install dependencies
RUN apt-get update -y && apt-get install -y --no-install-recommends \
    ca-certificates \
    wget \
    && rm -rf /var/lib/apt/lists/*

# Install Spark
RUN set -x \
    && wget --progress=bar:force https://archive.apache.org/dist/spark/spark-${SPARK_VERSION}/spark-${SPARK_VERSION}-bin-hadoop${HADOOP_VERSION}.tgz \
    && mkdir -p $SPARK_HOME \
    && tar -xzf spark-${SPARK_VERSION}-bin-hadoop${HADOOP_VERSION}.tgz --directory=$SPARK_HOME --strip 1 \
    && rm spark-${SPARK_VERSION}-bin-hadoop${HADOOP_VERSION}.tgz

# Replacing xbean jar to support openjdk-11
RUN apt-get install -y wget \
    && rm -rf /opt/spark-2.4.5/jars/xbean-asm6-shaded-4.8.jar \
    && wget https://repo1.maven.org/maven2/org/apache/xbean/xbean-asm6-shaded/4.10/xbean-asm6-shaded-4.10.jar -O /opt/spark-2.4.5/jars/xbean-asm6-shaded-4.10.jar

COPY files /root/

# Expose Spark ports
ENV SPARK_MASTER_PORT 7077
ENV SPARK_WEBUI_PORT 8080
EXPOSE ${SPARK_MASTER_PORT} ${SPARK_WEBUI_PORT}

ENTRYPOINT ["/root/entrypoint.sh"]
