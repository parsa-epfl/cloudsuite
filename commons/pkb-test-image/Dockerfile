FROM ubuntu:14.04

RUN set -x \
    && apt-get update -y && apt-get install -y --no-install-recommends \
      ca-certificates \
      curl \
      openssh-server \
    && curl -sSL https://get.docker.com/ | sh \
    && rm -rf /var/lib/apt/lists/*

# Configure sshd
RUN set -x \
    && mkdir /var/run/sshd \
    && sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd \
    && sed -i 's/PermitRootLogin without-password/PermitRootLogin yes/' /etc/ssh/sshd_config \
    && sed -i 's/UsePAM yes/UsePAM no/' /etc/ssh/sshd_config \
    && echo "UseDNS no" | tee -a /etc/ssh/sshd_config \
    && echo "MaxSessions 1000" | tee -a /etc/ssh/sshd_config

COPY keys/sshkey.pub /root/.ssh/authorized_keys
COPY entrypoint.sh /root/entrypoint.sh
RUN chmod +x /root/entrypoint.sh

EXPOSE 22

VOLUME /var/lib/docker

ENTRYPOINT ["/root/entrypoint.sh"]

