FROM ubuntu:16.04

ENV DEBIAN_FRONTEND noninteractive
#ENV http_proxy http://proxy-address:proxy-port
#ENV https_proxy https://proxy-address:proxy-port

RUN apt-get update                                                                     \
    && mkdir /scripts                                                                  \
    && apt-get install -y curl                                                         \
    && apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0xC2518248EEA14886 \
    && curl https://www.apache.org/dist/cassandra/KEYS | apt-key add -                 \
    && echo "deb http://ppa.launchpad.net/webupd8team/java/ubuntu xenial main"         \
        > /etc/apt/sources.list.d/webupd8team-ubuntu-java-xenial.list                  \
    && echo "deb http://www.apache.org/dist/cassandra/debian 30x main"                 \
        > /etc/apt/sources.list.d/cassandra.list                                       \
    && echo "oracle-java8-installer shared/accepted-oracle-license-v1-1 select true"   \
        | debconf-set-selections                                                       \
    && echo "oracle-java8-installer shared/accepted-oracle-license-v1-1 seen true"     \
        | debconf-set-selections                                                       \
    && apt-get update                                                                  \
    && apt-get install -y oracle-java8-installer cassandra

COPY set_sysctl.conf init_config.sh /scripts/

COPY jvm.options.128_GB /etc/cassandra/jvm.options

RUN /scripts/init_config.sh cassandra

RUN echo "Add nf_conntrack to modules ...\n"\
    && echo "nf_conntrack" >> /etc/modules \
    && echo "Add limits settings ...\n"\
    && echo "* soft nofile 1000000" >> /etc/security/limits.conf \
    && echo "* hard nofile 1000000" >> /etc/security/limits.conf

RUN cp /scripts/set_sysctl.conf /etc/sysctl.conf

ENV DEBIAN_FRONTEND teletype

CMD service cassandra start \
    && tail -f /dev/null
