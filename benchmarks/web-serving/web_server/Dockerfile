FROM ubuntu:20.04

USER root

RUN apt-get update && \
	apt-get install -y --force-yes software-properties-common && \
	apt-get update && \
	apt-get install --force-yes -y \
	build-essential \
	vim git \
	nginx \
	php7.4 php7.4-gd \
	php7.4-mysql php7.4-curl \
	php7.4-fpm php7.4-memcache \
  	php7.4-json php7.4-xml \
  	php7.4-pdo php7.4-mbstring 
#	hhvm

# Increase the open file limit
COPY files/limits.conf.append /tmp/
RUN cat /tmp/limits.conf.append >> /etc/security/limits.conf && rm -f /tmp/limits.conf.append

# Checkout the Elgg installation
COPY files/elgg_installation /usr/share/nginx/html/elgg

WORKDIR /usr/share/nginx/html
# Copy over the settings.php
COPY files/settings.php elgg/elgg-config/settings.php 

# Make the Elgg data directory
RUN mkdir /elgg_data
RUN chmod a+rw /elgg_data

# Copy over the Nginx Server configuration
COPY files/nginx_sites_avail.append /tmp/

# Copy over the Nginx HHVM Server configuration
COPY files/nginx_sites_avail_hhvm.append /tmp/
COPY files/configure_hhvm.sh /tmp/

RUN service nginx restart

RUN service php7.4-fpm start

ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

EXPOSE 8080

CMD ["/etc/bootstrap.sh", "-d"]
