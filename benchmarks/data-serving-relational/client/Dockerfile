FROM cloudsuite/base-os:ubuntu

ENV DEBIAN_FRONTEND noninteractive


# 1. install necessary software (sysbench)
RUN apt update && apt install git sysbench python3 -y

# 2. clone sysbench-tpcc's repo and install its script
RUN git clone https://github.com/Percona-Lab/sysbench-tpcc && cp sysbench-tpcc/*.lua /usr/share/sysbench/

# 3. Copy the template load file
COPY ./docker-entrypoint.py /root
COPY ./template/tpcc.py /root/template/tpcc.py
COPY ./template/oltp-rw.py /root/template/oltp-rw.py
COPY ./template/database.conf /root/template/database.conf
ENV DATABASE_CONF_FILE /root/template/database.conf

RUN chmod +x /root/docker-entrypoint.py 
RUN chmod +x /root/template/tpcc.py
RUN chmod +x /root/template/oltp-rw.py

ENTRYPOINT ["/root/docker-entrypoint.py"]
