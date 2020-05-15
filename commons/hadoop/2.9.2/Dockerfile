FROM cloudsuite/java:openjdk11

# Install dependencies
RUN apt-get update -y && apt-get install -y --no-install-recommends \
    curl \
    rsync \
    ssh \
    && rm -rf /var/lib/apt/lists/*

ENV HADOOP_VERSION 2.9.2
ENV HADOOP_PREFIX /opt/hadoop-${HADOOP_VERSION}
ENV HADOOP_CONF_DIR ${HADOOP_PREFIX}/etc/hadoop
ENV HADOOP_LOG_DIR ${HADOOP_PREFIX}/logs

# Install Hadoop
RUN set -x \
    && URL=https://downloads.apache.org/hadoop/common/hadoop-${HADOOP_VERSION}/hadoop-${HADOOP_VERSION}.tar.gz \
    && curl ${URL} | tar -xzC /opt

# Configure Hadoop
RUN JAVA_HOME=$(dirname $(dirname $(readlink -f $(which javac)))) \
    && sed -i "s:JAVA_HOME=.*:JAVA_HOME=${JAVA_HOME}:" ${HADOOP_PREFIX}/etc/hadoop/hadoop-env.sh

# Configure ssh
RUN ssh-keygen -t rsa -P '' -f /root/.ssh/id_rsa \
    && cat /root/.ssh/id_rsa.pub >> /root/.ssh/authorized_keys \
    && chmod 0600 /root/.ssh/authorized_keys \
    && echo "Host *\n  StrictHostKeyChecking no" > /root/.ssh/config

COPY files/*-site.xml ${HADOOP_PREFIX}/etc/hadoop/
COPY files/entrypoint.sh files/example_benchmark.sh /root/
RUN chmod +x /root/entrypoint.sh /root/example_benchmark.sh

ENTRYPOINT ["/root/entrypoint.sh"]
