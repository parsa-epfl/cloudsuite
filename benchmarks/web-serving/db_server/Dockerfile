FROM cloudsuitetest/mysql:mariadb-10.3

ENV root_password root

RUN apt-get update && apt-get install -y --no-install-recommends wget \
  && rm -rf /var/lib/apt/lists/*

COPY files/elgg_db_4_3.dump /elgg_bigDB.dump

ADD entrypoint.sh /etc/entrypoint.sh
RUN chown root:root /etc/entrypoint.sh
RUN chmod 700 /etc/entrypoint.sh

ENTRYPOINT ["/etc/entrypoint.sh"]
