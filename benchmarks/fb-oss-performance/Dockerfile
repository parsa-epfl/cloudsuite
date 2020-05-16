FROM ubuntu:16.04
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
    && apt-get install -y software-properties-common apt-transport-https \
    && apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0xB4112585D386EB94 \
    && add-apt-repository "deb https://dl.hhvm.com/ubuntu xenial-lts-3.30 main" \
    && apt-get update \
    && apt-get install -y hhvm=3.30.12-1~xenial

RUN apt-get update \
    && apt-get -y install git nginx unzip mysql-client util-linux coreutils	\
    && apt-get -y install autotools-dev \
    && apt-get -y install autoconf \
    && apt-get -y install software-properties-common build-essential 

RUN LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php \
    && apt-get -y install php7.0 php7.0-cgi php7.0-fpm \
    && apt-get -y install php7.0-mysql php7.0-curl php7.0-gd php7.0-intl php-pear php-imagick php7.0-imap php7.0-mcrypt php-memcache  php7.0-pspell php7.0-recode php7.0-sqlite3 php7.0-tidy php7.0-xmlrpc php7.0-xsl php7.0-mbstring php-gettext 

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

RUN git clone https://github.com/facebookarchive/oss-performance.git \ 
    && cd oss-performance \
    && git checkout tags/v2019.02.13.00 \
    && sed -i 's/3.24/3.30/g' composer.json \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && php composer.phar install \
    && hhvm composer.phar install

# Install perf
RUN apt-get update \
    && apt-get install -y linux-tools-common linux-tools-$(uname -r) linux-cloud-tools-$(uname -r)

ADD entrypoint.sh /oss-performance/entrypoint.sh
RUN chmod a+x /oss-performance/entrypoint.sh /oss-performance/scripts/perf.sh

# Replace with updated perf script in the fb-repo
ADD files/perf.sh /oss-performance/scripts/perf.sh

RUN useradd -ms /bin/bash fbwork \
    && mkdir -p /var/log/nginx \
    && chown -R fbwork:fbwork /var/log/nginx /oss-performance

USER fbwork

ENV HHVM_BIN /usr/bin/hhvm
ENV PHP_CGI_BIN /usr/bin/php-cgi
ENV PHP_CGI7_BIN /usr/bin/php-cgi7.0
ENV PHP_FPM7_BIN /usr/sbin/php-fpm7.0

WORKDIR /oss-performance
ENTRYPOINT ["./entrypoint.sh"]
