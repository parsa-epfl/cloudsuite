#!/bin/bash

# This script will be executed each time the uWSGI container is run.
# It is called with a single parameter ($1) - the container hostname (uwsgi)

echo "Starting uWSGI init script on container..."

IP_ADDR=$(grep "$1" /etc/hosts | awk '{print $1}')

cd /django-workload/django-workload || exit 1

if [ -f cluster_settings.py.bak ]; then
    cp -f cluster_settings.py.bak cluster_settings.py
else
    sed -e "s/DATABASES\['default'\]\['HOST'\] = 'localhost'/DATABASES\['default'\]\['HOST'\] = '$CASSANDRA_ENDPOINT'/g"                                  \
        -e "s/CACHES\['default'\]\['LOCATION'\] = '127.0.0.1:11811'/CACHES\['default'\]\['LOCATION'\] = '$MEMCACHED_ENDPOINT'/g"                          \
        -e "s/ALLOWED_HOSTS = \[/ALLOWED_HOSTS = \['$IP_ADDR', /g" \
        -e "s/STATSD_HOST = 'localhost'/STATSD_HOST = '$GRAPHITE_ENDPOINT'/g"                                                                             \
        -i cluster_settings.py

    PROC_NO=$(grep -c processor /proc/cpuinfo)
    sed -i "s/processes = 88/processes = $PROC_NO/g" uwsgi.ini

    #Add uwsgi ip in allowed hosts
    if ! grep "ALLOWED_HOSTS" cluster_settings.py | grep -q $UWSGI_ENDPOINT; then
        sed -i -e "/^ALLOWED_HOSTS = / s/.$/, \'$UWSGI_ENDPOINT\'\]/" cluster_settings.py
    fi

    cp cluster_settings.py cluster_settings.py.bak
fi

. venv/bin/activate

DJANGO_SETTINGS_MODULE=cluster_settings django-admin setup &> django-admin.log

uwsgi uwsgi.ini &

deactivate

tail -f /dev/null
