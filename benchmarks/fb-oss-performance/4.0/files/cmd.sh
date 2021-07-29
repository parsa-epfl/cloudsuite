#!/bin/bash

# mysql server ip
MYSQL_IP=10.52.3.70
SIEGE_IP=10.52.3.70

# Max connections >1000 required for mediawiki workload. If mysql already has max_connections > 1000 comment the below line.
mysql --host=$MYSQL_IP --user=root --password=root -e "SET GLOBAL max_connections = 1001;"

# run hhvm
hhvm perf.php --i-am-not-benchmarking --mediawiki --db-host=$MYSQL_IP --db-username=root --db-password=root --hhvm=$HHVM_BIN --remote-siege=root@$SIEGE_IP

# run php_cgi7
# hhvm perf.php --i-am-not-benchmarking --mediawiki --db-host=$MYSQL_IP --db-username=root --db-password=root --php5=$PHP_CGI7_BIN --remote-siege=root@$SIEGE_IP

# run php_fpm7
# hhvm perf.php --i-am-not-benchmarking --mediawiki --db-host=$MYSQL_IP --db-username=root --db-password=root --php=$PHP_FPM7_BIN --remote-siege=root@$SIEGE_IP
