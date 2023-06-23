FROM cloudsuite/postgresql:15

# Install sudo for user switching
RUN apt update && apt install sudo python3 -y

# Make the database access public
RUN echo 'host\tall\tcloudsuite\t0.0.0.0/0\tscram-sha-256' >> /etc/postgresql/15/main/pg_hba.conf

# Copy the entrypoint
COPY ./docker-entrypoint.py /root

RUN chmod +x /root/docker-entrypoint.py

ENTRYPOINT ["/root/docker-entrypoint.py"]
