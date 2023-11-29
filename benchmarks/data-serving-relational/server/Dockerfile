FROM cloudsuite/postgresql:15

# Install sudo for user switching
RUN set -eux ;  \
    apt update ; \
    apt install python3 gosu -y ; \
    rm -rf /var/lib/apt/lists/* ; \
    gosu postgres true

# Make the database access public
RUN echo 'host\tall\tcloudsuite\t0.0.0.0/0\tscram-sha-256' >> /etc/postgresql/15/main/pg_hba.conf

# Copy the entrypoint
COPY ./docker-entrypoint.py /root

RUN chmod +x /root/docker-entrypoint.py

ENTRYPOINT ["/root/docker-entrypoint.py"]
