FROM cloudsuite/debian:base-os

LABEL maintainer="Mark Sutherland <mark.sutherland@epfl.ch>"

ENV DEBIAN_FRONTEND noninteractive
ENV root_password root

RUN echo mysql-server mysql-server/root_password password ${root_password} |  /usr/bin/debconf-set-selections	
RUN echo mysql-server mysql-server/root_password_again password ${root_password} |  /usr/bin/debconf-set-selections
	
RUN apt-get update \
    && apt-get install -y default-mysql-server

# Allow it to listen from outer world	
RUN echo "[mysqld]" >> /etc/mysql/my.cnf
RUN echo "bind-address = 0.0.0.0" >> /etc/mysql/my.cnf

# Copy the scripts
ADD files/execute.sh /execute.sh
ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

ENTRYPOINT ["/etc/bootstrap.sh"]
