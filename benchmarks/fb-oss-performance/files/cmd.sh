# mysql 
mysql -h10.52.2.161 -uroot -proot -e "SET GLOBAL max_connections = 1001;"

# run hhvm 
hhvm perf.php --i-am-not-benchmarking \
    --mediawiki \
    --db-host=10.52.2.161 --db-username=root --db-password=root \
    --hhvm=$HHVM_BIN \
    --exec-after-warmup="/oss-performance/scripts/perf.sh \
        -e instructions,context-switches,branch-instructions,branch-misses,cpu-cycles,LLC-load-misses,LLC-loads,LLC-store-misses,LLC-stores,dTLB-load-misses,dTLB-loads,dTLB-store-misses,dTLB-stores,iTLB-load-misses,iTLB-loads"
