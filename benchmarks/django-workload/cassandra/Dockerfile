FROM cloudsuite/cassandra:3.11.3


COPY files/* /scripts/
COPY files/jvm.options /etc/cassandra/jvm.options


RUN cp /scripts/set_sysctl.conf /etc/sysctl.conf


ENTRYPOINT ["/scripts/bootstrap.sh"]

