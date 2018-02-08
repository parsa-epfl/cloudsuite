#!/bin/bash

update-rc.d hhvm defaults
cat /etc/nginx/sites-enabled/default ~/nginx_backup
cat /tmp/nginx_sites_avail_hhvm.append >> /etc/nginx/sites-available/default
/usr/share/hhvm/install_fastcgi.sh
echo "hhvm.server.allow_run_as_root = true" >> /etc/hhvm/server.ini
/usr/bin/hhvm --config /etc/hhvm/php.ini --config /etc/hhvm/server.ini --mode daemon -vPidFile=/var/run/hhvm/pid

