FROM ubuntu:18.04
#Original maintainer: MAINTAINER Tapti Palit <tpalit@cs.stonybrook.edu>
LABEL maintainer="Alexandros Daglis <alexandros.daglis@epfl.ch>"

RUN apt-get update -y && apt-get install -y --no-install-recommends \
      nginx=1.14.0-0ubuntu1 \
    && rm -rf /var/lib/apt/lists/*

# Increase the open file limit
COPY files/limits.conf.append /tmp/
RUN cat /tmp/limits.conf.append >> /etc/security/limits.conf

# Update nginx to serve /videos
RUN sed -i 's|/var/www/html|/videos|g' /etc/nginx/sites-available/default

CMD ["nginx", "-g", "daemon off;"]
