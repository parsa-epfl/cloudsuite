FROM cloudsuite/cassandra:3.11

COPY files /scripts

RUN chmod +x /scripts/docker-entrypoint.sh

ENTRYPOINT ["/scripts/docker-entrypoint.sh"]

CMD ["cassandra", "-R", "-f"]

EXPOSE 7000 7001 7199 9042 9160
