FROM ubuntu:18.04

ENV DEBIAN_FRONTEND noninteractive
#ENV http_proxy http://proxy-address:proxy-port
#ENV https_proxy https://proxy-address:proxy-port

RUN mkdir /scripts
COPY files/ /django-workload/

RUN apt-get update
RUN apt-get install -y git python3-virtualenv python3-dev  \
        python-pip libmemcached-dev zlib1g-dev netcat-openbsd
RUN cd /django-workload/django-workload                       \
    && python3 -m virtualenv -p python3  venv                 \
    && . venv/bin/activate                                    \
    && pip install -r requirements.txt                        \
    && deactivate                                             \
    && cp cluster_settings_template.py cluster_settings.py

COPY files/set_sysctl.conf files/uwsgi_init.sh /scripts/
RUN echo "Add nf_conntrack to modules ...\n"\
    && echo "nf_conntrack" >> /etc/modules \
    && echo "Add limits settings ...\n"\
    && echo "root soft nofile 1000000" >> /etc/security/limits.conf \
    && echo "root hard nofile 1000000" >> /etc/security/limits.conf

RUN cp /scripts/set_sysctl.conf /etc/sysctl.conf

ENV DEBIAN_FRONTEND teletype

CMD /scripts/uwsgi_init.sh uwsgi
