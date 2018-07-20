FROM ubuntu:14.04
#Original maintainer: Tapti Palit <tpalit@cs.stonybrook.edu>
LABEL maintainer="Alexandros Daglis <alexandros.daglis@epfl.ch>"

RUN apt-get update -y && apt-get install -y --no-install-recommends \
      bc \
      build-essential \
      libssl-dev \
    && rm -rf /var/lib/apt/lists/*

COPY files /root/
RUN set -x \
    && chmod +x /root/docker-entrypoint.sh \
    && mkdir -p /root/build \
    && cd /root/build \
    && /root/videoperf/configure \
    && make \
    && make install

RUN mkdir /output
VOLUME ["/output"]

ENTRYPOINT ["/root/docker-entrypoint.sh"]
