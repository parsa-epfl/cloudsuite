FROM cloudsuite/debian:base-os

ENV DEBIAN_FRONTEND noninteractive

# Install sshd
RUN set -eux; \
    ARCH="$(dpkg --print-architecture)"; \
    case "${ARCH}" in \
       aarch64|arm64|amd64|x86_64) \
         GCC_VER='7'; \
        ;; \
       riscv64) \
         GCC_VER='8'; \
        ;; \
       *) \
         echo "Unsupported arch: ${ARCH}"; \
         exit 1; \
         ;; \
    esac; \
    apt-get update && apt-get install -y gcc-${GCC_VER} g++-${GCC_VER} openssh-server git autotools-dev autoconf make \
    && rm -rf /var/lib/apt/lists/* \
    && update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-${GCC_VER} ${GCC_VER} \
    && update-alternatives --install /usr/bin/g++ g++ /usr/bin/g++-${GCC_VER} ${GCC_VER} \
    && update-alternatives --config gcc \
    && update-alternatives --config g++

RUN git clone https://github.com/JoeDog/siege.git \
    && cd siege \
    && git checkout tags/v4.0.3rc3 \
    && ./utils/bootstrap \
    && automake --add-missing \
    && ./configure \
    && make \
    && make uninstall \
    && make install \
    && cd .. \
    && rm -rf siege

# Modify `sshd_config`
RUN sed -i -e 's/.*PermitEmptyPasswords.*/PermitEmptyPasswords yes/' -e 's/.*PermitRootLogin.*/PermitRootLogin yes/' -e 's/.*Port.*/Port 9801/' -e 's/^UsePAM yes/UsePAM no/' /etc/ssh/sshd_config \
    && /etc/init.d/ssh start \
    && passwd -d root

ADD entrypoint.sh /entrypoint.sh
RUN chown root:root /entrypoint.sh \
    && chmod 700 /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
