FROM cloudsuite/hhvm:3.30
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update \
    && apt-get -y install git nginx unzip mariadb-client util-linux coreutils wget lsb-release apt-transport-https ca-certificates \
    && apt-get -y install autotools-dev \
    && apt-get -y install autoconf \
    && apt-get -y install software-properties-common build-essential

RUN wget https://packages.sury.org/php/apt.gpg \
    && apt-key add apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php7.list \
    && apt update \
    && apt-get -y install php7.3 php7.3-cgi php7.3-fpm \
    && apt-get -y install php7.3-mysql php7.3-curl php7.3-gd php7.3-intl php-pear php-imagick php7.3-imap php-memcache  php7.3-pspell php7.3-recode php7.3-sqlite3 php7.3-tidy php7.3-xmlrpc php7.3-mbstring php-gettext

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

ADD entrypoint.sh /oss-performance/entrypoint.sh
RUN chmod a+x /oss-performance/entrypoint.sh

RUN useradd -ms /bin/bash fbwork \
    && mkdir -p /var/log/nginx \
    && chown -R fbwork:fbwork /var/log/nginx /oss-performance

RUN sed -i -e '$a\    StrictHostKeyChecking no' -e '$a\    UserKnownHostsFile=/dev/null' -e 's/.*Port.*/Port 9801/' /etc/ssh/ssh_config

USER fbwork

ENV PHP_CGI_BIN /usr/bin/php-cgi
ENV PHP_CGI7_BIN /usr/bin/php-cgi7.3
ENV PHP_FPM7_BIN /usr/sbin/php-fpm7.3

WORKDIR /oss-performance
ENTRYPOINT ["./entrypoint.sh"]
