FROM cloudsuite/java

# Install dependencies
RUN apt-get update -y && apt-get install -y --no-install-recommends \
      curl \
      rsync \
      ssh \
    && rm -rf /var/lib/apt/lists/*

ENV HADOOP_VERSION 2.7.6
ENV HADOOP_PREFIX /opt/hadoop-${HADOOP_VERSION}
ENV HADOOP_CONF_DIR ${HADOOP_PREFIX}/etc/hadoop
ENV HADOOP_LOG_DIR ${HADOOP_PREFIX}/logs

# Install Hadoop
RUN set -x \
    && MIRROR=$(curl -s http://www.apache.org/dyn/closer.lua?as_json=1 | grep preferred | awk '{print $2}' | sed -e 's/"//g') \
    && URL=${MIRROR}hadoop/common/hadoop-${HADOOP_VERSION}/hadoop-${HADOOP_VERSION}.tar.gz \
    && curl ${URL} | tar -xzC /opt

# Configure Hadoop
RUN sed -i "s:JAVA_HOME=.*:JAVA_HOME=${JAVA_HOME}:" ${HADOOP_PREFIX}/etc/hadoop/hadoop-env.sh

# Configure ssh
RUN ssh-keygen -t rsa -P '' -f /root/.ssh/id_rsa \
    && cat /root/.ssh/id_rsa.pub >> /root/.ssh/authorized_keys \
    && chmod 0600 /root/.ssh/authorized_keys \
    && echo "Host *\n  StrictHostKeyChecking no" > /root/.ssh/config

COPY files/*-site.xml ${HADOOP_PREFIX}/etc/hadoop/
COPY files/entrypoint.sh files/example_benchmark.sh /root/
RUN chmod +x /root/entrypoint.sh /root/example_benchmark.sh

ENTRYPOINT ["/root/entrypoint.sh"]
