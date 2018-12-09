FROM cloudsuite/java

ENV DEBIAN_FRONTEND noninteractive
#ENV http_proxy http://proxy-address:proxy-port
#ENV https_proxy https://proxy-address:proxy-port

ENV CASSANDRA_CONFIG /etc/cassandra

RUN apt-get update                                                                     \
    && mkdir /scripts                                                                  \
    && apt-get install -y curl                                                         \
    && apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0xC2518248EEA14886 \
    && curl https://www.apache.org/dist/cassandra/KEYS | apt-key add -                 \
    && echo "deb http://ppa.launchpad.net/webupd8team/java/ubuntu xenial main"         \
        > /etc/apt/sources.list.d/webupd8team-ubuntu-java-xenial.list                  \
    && echo "deb http://www.apache.org/dist/cassandra/debian 30x main"                 \
        > /etc/apt/sources.list.d/cassandra.list                                       \
    && apt-get update                                                                  \
    && apt-get install -y cassandra



RUN echo "Add limits settings ...\n"\
    && echo "* soft nofile 1000000" >> /etc/security/limits.conf \
    && echo "* hard nofile 1000000" >> /etc/security/limits.conf


ENV DEBIAN_FRONTEND teletype

