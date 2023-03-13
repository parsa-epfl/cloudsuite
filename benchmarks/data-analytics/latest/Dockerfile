FROM cloudsuite/hadoop:2.10.2

ENV MAHOUT_VERSION 14.1
ENV MAHOUT_HOME /opt/mahout-${MAHOUT_VERSION}
RUN mkdir ${MAHOUT_HOME} /user

# Install Mahout
RUN set -x \
    && URL=https://downloads.apache.org/mahout/${MAHOUT_VERSION}/apache-mahout-distribution-${MAHOUT_VERSION}.tar.gz \
    && curl ${URL} | tar -xzC ${MAHOUT_HOME} 

COPY files/benchmark.sh /root/
COPY files/docker-entrypoint.py /root/
COPY files/mahout-examples-0.13.0-job.jar ${MAHOUT_HOME}/.
COPY files/categories /user/

RUN chmod +x /root/benchmark.sh /root/docker-entrypoint.py \
    && ln -s /root/benchmark.sh /bin/benchmark

ENTRYPOINT ["/root/docker-entrypoint.py"]
