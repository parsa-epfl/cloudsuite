FROM ubuntu:22.04

ENV DEBIAN_FRONTEND noninteractive


# 1. install necessary software (sysbench)
RUN apt update && apt install git sysbench -y

# 2. clone sysbench-tpcc's repo and install its script
RUN git clone https://github.com/Percona-Lab/sysbench-tpcc && cp sysbench-tpcc/*.lua /usr/share/sysbench/

# 3. Copy the template load file
COPY ./template /
