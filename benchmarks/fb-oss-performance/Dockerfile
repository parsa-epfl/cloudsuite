FROM hhvm/hhvm:3.24.0
ENV DEBIAN_FRONTEND noninteractive
RUN hhvm --version

RUN apt-get update \
    && apt-get -y install nginx unzip mysql-server util-linux coreutils	\
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
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');"\
    && php composer.phar install

RUN cd oss-performance \
    && hhvm composer.phar install

RUN useradd -ms /bin/bash fbwork \
    && mkdir -p /var/log/nginx \
    && chown -R fbwork:fbwork /var/log/nginx

USER fbwork
WORKDIR /oss-performance

ENTRYPOINT bash -x cmd.sh
