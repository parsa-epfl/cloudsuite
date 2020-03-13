FROM ubuntu:14.04
LABEL maintainer="Mark Sutherland <mark.sutherland@epfl.ch>"

ENV root_password root
ENV web_host web_server 
ENV MYSQL_VERSION 0.8.10-1
# Install MySQL
RUN echo mysql-server mysql-server/root_password password ${root_password} | sudo debconf-set-selections
RUN echo mysql-server mysql-server/root_password_again password ${root_password} | sudo debconf-set-selections


RUN apt-get update \
	&& apt-get install -y --no-install-recommends wget build-essential\
	&& wget -O /tmp/mysql_${MYSQL_VERSION}_all.deb https://dev.mysql.com/get/mysql-apt-config_${MYSQL_VERSION}_all.deb \
	&& dpkg -i /tmp/mysql_${MYSQL_VERSION}_all.deb \
	&& rm -rf /tmp/mysql_${MYSQL_VERSION}_all.deb

#RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
#	mysql-server

# Allow it to listen from outer world
#RUN sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/mysql/my.cnf

# Copy the scripts
ADD files/execute.sh /execute.sh

ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

ENTRYPOINT ["/etc/bootstrap.sh"]
