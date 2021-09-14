FROM cloudsuite/mysql:mariadb-10.3

ENV root_password root

ADD files/elgg_db_dump.txt /elgg_db_dump.txt

ADD entrypoint.sh /etc/entrypoint.sh
RUN chown root:root /etc/entrypoint.sh
RUN chmod 700 /etc/entrypoint.sh

ENTRYPOINT ["/etc/entrypoint.sh"]
