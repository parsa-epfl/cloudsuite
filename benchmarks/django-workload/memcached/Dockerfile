FROM cloudsuite/memcached:1.6.6

COPY files/* /scripts/
USER root
RUN cp /scripts/set_sysctl.conf /etc/sysctl.conf

ENTRYPOINT ["memcached"]
USER memcache
EXPOSE 11211

CMD ["-t", "16", "-m", "5520", "-n", "550", "-l","0.0.0.0"]
