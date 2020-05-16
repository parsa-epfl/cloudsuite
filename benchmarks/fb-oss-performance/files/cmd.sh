#!/bin/bash

MYSQL_IP=10.52.3.70
PERF_RUN="/oss-performance/scripts/perf.sh -e instructions,cycles,branch-misses,L1-icache-misses,L1-dcache-misses,cache-misses,LLC-store-misses,iTLB-misses,dTLB-misses,dTLB-load-misses,dTLB-store-misses"

# Max connections >1000 required for mediawiki workload
mysql --host=$MYSQL_IP --user=root --password=root -e "SET GLOBAL max_connections = 1001;"

# run hhvm
hhvm perf.php --i-am-not-benchmarking --mediawiki --db-host=$MYSQL_IP --db-username=root --db-password=root --exec-after-warmup="$PERF_RUN" --hhvm=$HHVM_BIN

# run php_cgi7
# hhvm perf.php --i-am-not-benchmarking --mediawiki --db-host=$MYSQL_IP --db-username=root --db-password=root --exec-after-warmup="$PERF_RUN" --php5=$PHP_CGI7_BIN

# run php_fpm7
# hhvm perf.php --i-am-not-benchmarking --mediawiki --db-host=$MYSQL_IP --db-username=root --db-password=root --exec-after-warmup="$PERF_RUN" --php=$PHP_FPM7_BIN
