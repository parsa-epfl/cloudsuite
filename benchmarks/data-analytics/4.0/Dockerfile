FROM cloudsuite/hadoop:2.10.1

ENV MAHOUT_VERSION 0.13.0
ENV MAHOUT_HOME /opt/mahout-${MAHOUT_VERSION}

# Install dependencies
RUN apt-get update -y && apt-get install -y --no-install-recommends \
    bzip2 \
    && rm -rf /var/lib/apt/lists/*

# Install Mahout
RUN set -x \
    && URL=https://downloads.apache.org/mahout/${MAHOUT_VERSION}/apache-mahout-distribution-${MAHOUT_VERSION}.tar.gz \
    && curl ${URL} | tar -xzC /opt \
    && mv /opt/apache-mahout-distribution-${MAHOUT_VERSION} ${MAHOUT_HOME}

# Download dataset
# Use latest_link=$(curl -s https://dumps.wikimedia.org/enwiki/latest/ | grep  "enwiki-latest-pages-articles1.xml-" | grep -Eoi '<a [^>]+>' | cut -d '"' -f 2 | grep -E "*.bz2$") \
#     && curl https://dumps.wikimedia.org/enwiki/latest/${latest_link} | bunzip2 > /root/wiki - to get the latest link and download.
RUN curl https://dumps.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles1.xml-p1p41242.bz2 | bunzip2 > /root/wiki

COPY files/*-site.xml ${HADOOP_CONF_DIR}/
COPY files/categories /root/
COPY files/benchmark.sh /root/
RUN chmod +x /root/benchmark.sh && ln -s /root/benchmark.sh /bin/benchmark

# Set JVM heap size to 1GB
ENV HADOOP_CLIENT_OPTS -Xmx1024m
