#FROM cloudsuite/mysql:mariadb-10.3
FROM cloudsuitetest/mysql:mariadb-10.3-debian 

ENV root_password root

RUN apt-get update && apt-get install -y --no-install-recommends wget mariadb-backup \
  && rm -rf /var/lib/apt/lists/*

# it should be a download from an external link
#COPY files/elgg_db_4_3.dump /elgg_bigDB.dump
COPY files/backup /.

ADD entrypoint.sh /etc/entrypoint.sh
RUN chown root:root /etc/entrypoint.sh
RUN chmod 700 /etc/entrypoint.sh

ENTRYPOINT ["/etc/entrypoint.sh"]

#CMD ["/usr/sbin/mysqld"]
