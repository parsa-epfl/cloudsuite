FROM cloudsuite/mysql:mariadb-10.6

ENV root_password root
ENV DB_URL http://datasets.epfl.ch/cloudsuite/ELGG_DB.tar.gz

RUN apt-get update && apt-get install -y --no-install-recommends wget mariadb-backup \
  && rm -rf /var/lib/apt/lists/*

ADD entrypoint.sh /etc/entrypoint.sh
RUN chown root:root /etc/entrypoint.sh
RUN chmod 700 /etc/entrypoint.sh

ENTRYPOINT ["/etc/entrypoint.sh"]
