FROM cloudsuite/base-os:ubuntu

# 1. install postgresql

ENV DEBIAN_FRONTEND noninteractive

RUN apt update && apt install -y wget gnupg lsb-release && \
    echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list && \
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
    apt update && \
    apt -y install postgresql-15


# 2. start the database
CMD ["service", "postgresql", "start"]

