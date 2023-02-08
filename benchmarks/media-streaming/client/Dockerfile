FROM cloudsuite/base-os:ubuntu

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    bc \
    build-essential \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

COPY files /root/
RUN set -x \
    && mkdir -p /root/build \
    && cd /root/build \
    && /root/videoperf/configure \
    && make \
    && make install

RUN chmod +x /root/docker-entrypoint.sh

RUN rm -rf /output && mkdir -p /output
VOLUME ["/output"]

RUN rm -rf /videos/logs && mkdir -p /videos/logs
VOLUME [ "/videos/logs" ]

ENTRYPOINT ["/root/docker-entrypoint.sh"]
