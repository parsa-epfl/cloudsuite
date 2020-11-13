FROM cloudsuite/debian:base-os
ENV DEBIAN_FRONTEND noninteractive
ENV HHVM_DISABLE_NUMA true

RUN apt-get update \
    && apt-get install -y software-properties-common apt-transport-https gnupg2 \
    && apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xB4112585D386EB94 \
    && add-apt-repository -s https://dl.hhvm.com/debian \
    && apt-get update \
    && apt-get build-dep hhvm-nightly -y

RUN git clone git://github.com/facebook/hhvm.git \
    && cd hhvm \
    && git checkout HHVM-3.30 \
    && git submodule update --init --recursive \
    && cmake -DMYSQL_UNIX_SOCK_ADDR=/var/run/mysqld/mysqld.sock . \
    && make -j $(nproc) \
    && make install \
    && cd .. && rm -rf /hhvm

ENV HHVM_BIN /usr/local/bin/hhvm
