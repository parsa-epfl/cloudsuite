FROM cloudsuite/postgresql:15

# Install sudo for user switching
RUN apt update && apt install sudo

# Make the database access public
RUN sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" /etc/postgresql/15/main/postgresql.conf
RUN echo 'host\tall\tcloudsuite\t0.0.0.0/0\tscram-sha-256' >> /etc/postgresql/15/main/pg_hba.conf

# Copy the entrypoint
COPY ./entrypoint.sh /

ENTRYPOINT /entrypoint.sh
