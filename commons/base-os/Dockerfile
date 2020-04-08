FROM --platform=linux/amd64 debian:sid AS base

ARG TARGETPLATFORM
ENV TARGETPLATFORM=${TARGETPLATFORM:-linux/amd64}

RUN apt-get update \
        && apt-get install -y --no-install-recommends \
        ca-certificates \
        debootstrap \
        wget \
        xz-utils

RUN set -ex \
        && case "$TARGETPLATFORM" in \
        # riscv64 debian sid
        linux/riscv64) \
        echo "deb http://ftp.ports.debian.org/debian-ports sid main" >> /etc/apt/sources.list \
        && apt-get install -y debian-ports-archive-keyring \
        && dpkg --add-architecture riscv64 \
        && apt-get update \
        && debootstrap \
        --keyring=/usr/share/keyrings/debian-ports-archive-keyring.gpg \
        --arch=riscv64 \
        --variant=minbase \
        --include=debian-ports-archive-keyring \
        sid /rootfs http://ftp.ports.debian.org/debian-ports \
        && rm -rf /rootfs/var/log/dpkg.log /rootfs/var/log/bootstrap.log /rootfs/var/log/alternatives.log \
        && rm -rf /rootfs/var/cache/ldconfig/aux-cache /rootfs/var/cache/apt/*  \
        && rm -rf /rootfs/var/lib/apt/lists/* \
        ;; \
        # arm64v8 debian buster
        linux/arm64) \
        mkdir -p /rootfs \
        && wget --progress=bar:force https://raw.githubusercontent.com/debuerreotype/docker-debian-artifacts/dist-arm64v8/buster/rootfs.tar.xz -O /rootfs-arm64v8.tar.xz \
        && tar -xf /rootfs-arm64v8.tar.xz -C /rootfs \
        ;; \
        # amd64 debian buster
        linux/amd64) \
        mkdir -p /rootfs \
        && wget --progress=bar:force https://raw.githubusercontent.com/debuerreotype/docker-debian-artifacts/dist-amd64/buster/rootfs.tar.xz -O /rootfs-amd64.tar.xz \
        && tar -xf /rootfs-amd64.tar.xz -C /rootfs \
        ;; \
        *) \
        echo "unsupported arch" \
        ;; \
        esac;

FROM scratch
COPY --from=base /rootfs/ /
CMD ["bash"]

