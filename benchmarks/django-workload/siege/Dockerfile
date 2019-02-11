FROM ubuntu:18.04

ENV DEBIAN_FRONTEND noninteractive
#ENV http_proxy http://proxy-address:proxy-port
#ENV https_proxy https://proxy-address:proxy-port

COPY files/ /home/tester/django-workload

RUN useradd -m tester
RUN apt-get update
RUN apt-get -y install git siege python3 python3-numpy netcat-openbsd
RUN cd /home/tester/django-workload/                                     \
    && echo "failures = 1000000" > /home/tester/.siegerc                 \
    && echo "protocol = HTTP/1.1" >> /home/tester/.siegerc               \
    && mkdir -p /home/tester/scripts

COPY files/siege_init.sh files/set_sysctl.conf /home/tester/scripts/

RUN chown -R tester:tester /home/tester \
    && echo "Add nf_conntrack to modules ...\n"\
    && echo "nf_conntrack" >> /etc/modules \
    && echo "Add limits settings ...\n"\
    && echo "* soft nofile 1000000" >> /etc/security/limits.conf \
    && echo "* hard nofile 1000000" >> /etc/security/limits.conf

RUN cp /home/tester/scripts/set_sysctl.conf /etc/sysctl.conf

ENV DEBIAN_FRONTEND teletype

CMD /home/tester/scripts/siege_init.sh
