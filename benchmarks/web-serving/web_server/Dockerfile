FROM cloudsuite/base-os:ubuntu
ENV DEBIAN_FRONTEND noninteractive

USER root

ENV VERSION 8.1

RUN apt-get update && \
    apt-get install -y --force-yes software-properties-common && \
# if you want to install versions other than 8.1, uncomment the following lines
#    add-apt-repository ppa:ondrej/php && \
#    apt-get update && \
    apt-get install -y --force-yes \
    build-essential \
    vim git wget \
    nginx \
    php${VERSION} php${VERSION}-gd \
    php${VERSION}-mysql php${VERSION}-curl \
    php${VERSION}-fpm php${VERSION}-memcached\
    php${VERSION}-xml \
    php${VERSION}-pdo php${VERSION}-mbstring 
#    php${VERSION}-json   

# Increase the open file limit
COPY files/limits.conf.append /tmp/
RUN cat /tmp/limits.conf.append >> /etc/security/limits.conf && rm -f /tmp/limits.conf.append

# Checkout the Elgg installation
RUN mkdir /usr/share/nginx/html/elgg && \ 
    wget --progress=bar:force -O - http://datasets.epfl.ch/cloudsuite/elgg_installation_4.3.0.tar.gz | \
    tar zxvf - -C ~ && \
    mv ~/elgg_installation_4.3.0/* /usr/share/nginx/html/elgg/.
   
WORKDIR /usr/share/nginx/html

# Copy over the settings.php
COPY files/settings_4.3.php elgg/elgg-config/settings.php

# Make the Elgg data directory
RUN mkdir /elgg_data
RUN chmod a+rw /elgg_data

# Copy over the Nginx Server configuration
COPY files/nginx_sites_avail_pt.append /tmp/
COPY files/nginx_sites_avail_tls.append /tmp/


RUN service nginx restart

RUN service php${VERSION}-fpm start

ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

EXPOSE 8080
EXPOSE 8443

CMD ["/etc/bootstrap.sh", "-d"]
