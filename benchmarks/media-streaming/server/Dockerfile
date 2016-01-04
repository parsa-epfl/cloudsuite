FROM ubuntu:14.04
#Original maintainer: MAINTAINER Tapti Palit <tpalit@cs.stonybrook.edu>
MAINTAINER Alexandros Daglis <alexandros.daglis@epfl.ch>

RUN apt-get update && apt-get install -y \
	build-essential \
	git \
	nginx

# Increase the open file limit
COPY files/limits.conf.append /tmp/
RUN cat /tmp/limits.conf.append >> /etc/security/limits.conf && rm -f /tmp/limits.conf.append

# Update NGINX to server /streaming_server/videos
RUN sed -i 's/\/usr\/share\/nginx\/html/\/videos/g' /etc/nginx/sites-available/default

COPY files/docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

#CMD bash
