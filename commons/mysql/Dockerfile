FROM cloudsuite/debian:base-os
LABEL maintainer="Mark Sutherland <mark.sutherland@epfl.ch>"

ENV MYSQL_MAJOR 5.7
ENV MYSQL_VERSION 5.7.29-1debian10
ENV root_password root

RUN apt-get update \
	&& apt-get install -y --no-install-recommends gnupg dirmngr \
	&& rm -rf /var/lib/apt/lists/*

# Install MySQL
RUN { \
	echo mysql-community-server mysql-community-server/data-dir select ''; \
	echo mysql-community-server mysql-community-server/root-pass password ''; \
	echo mysql-community-server mysql-community-server/re-root-pass password ''; \
	echo mysql-community-server mysql-community-server/remove-test-db select false; \
	} | debconf-set-selections \
	&& echo "deb http://repo.mysql.com/apt/debian/ buster mysql-${MYSQL_MAJOR}" > /etc/apt/sources.list.d/mysql.list \
	&& apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 8C718D3B5072E1F5 \
	&& apt-get update && DEBIAN_FRONTEND=noninteractive \
	&& apt-get install -y mysql-server="${MYSQL_VERSION}" \
	&& rm -rf /var/lib/apt/lists/*

# Allow it to listen from outer world
RUN echo "bind-address = 0.0.0.0" >>/etc/mysql/mysql.conf.d/mysqld.cnf 

# Copy the scripts
ADD files/execute.sh /execute.sh
ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

ENTRYPOINT ["/etc/bootstrap.sh"]
