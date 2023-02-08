FROM cloudsuite/base-os:ubuntu


ENV DEBIAN_FRONTEND noninteractive
ENV root_password root

RUN echo mariadb-server-10.6 mysql-server/root_password password ${root_password} |  /usr/bin/debconf-set-selections
RUN echo mariadb-server-10.6 mysql-server/root_password_again password ${root_password} |  /usr/bin/debconf-set-selections

RUN apt update -q \
    && apt install -y mariadb-server

# Allow it to listen from outer world	
RUN echo "[mysqld]" >> /etc/mysql/my.cnf
RUN echo "user = mysql" >> /etc/mysql/my.cnf
RUN echo "bind-address = 0.0.0.0" >> /etc/mysql/my.cnf

# Copy the scripts
ADD files/execute.sh /execute.sh
ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

ENTRYPOINT ["/etc/bootstrap.sh"]
