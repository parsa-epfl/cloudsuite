FROM cloudsuite/cassandra:4.1.0

RUN apt update && apt install -y --no-install-recommends python3-yaml && rm -rf /var/lib/apt/lists/*

COPY docker-entrypoint.py /

ENTRYPOINT ["/docker-entrypoint.py"]

EXPOSE 7000 7001 7199 9042 9160
