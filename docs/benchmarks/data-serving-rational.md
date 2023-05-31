# Data Serving (PostgreSQL)

The Data Serving benchmark (based on PostgreSQL 15) uses [sysbench][sysbench] and [sysbench-tpcc][sysbench-tpcc] as a load generator. With several different [aspects][difference-tpcc] from the standard TPC-C, it can still reflect most of the important properties of the TPC-C workload. One of the most widely used workloads from sysbench (`oltp_read_write`) is also wrapped for measurement.

### Dockerfiles
Supported tags and their respective `Dockerfile` links:
    - `server` contains PostgreSQL 15, and by default it opens the prompt using user `postgres`.
    - `client` contains sysbench, sysbench-tpcc, and template load generation script to run the workload.

### Server Container

Start the server container with the following command:

```bash

$ docker run --name postgresql-server -it --net host cloudsuite/data-serving-rational:server

```

It creates a database user `cloudsuite` (password is `cloudsuite` as well), a database `sbtest`, and grant database's permission to the user. The user has the permission to access the database remotely. 


### Client Container

Start the client container with the following command:

```bash

$ docker run --name sysbench-client -it --net host cloudsuite/data-serving-rational:client

```

Under the root folder `/` you can find the following files:
- `database.conf` defines the port to the PostgreSQL. You can modify the IP address and the port accordingly based on the configuration of your server container.
- `{oltp_read_write,tpcc}.warmup.sh` is the script to warm up the database by filling its content. As a template, you can change the table counts, the rows per table (for `oltp_read_write`), the scale (for `tpcc`), and the number of threads for the client. More options can be added by referring `sysbench --help` and the help of each workload.
- `{oltp_read_write,tpcc}.run.sh` is the script to load the database. This script should be run after the database is populated, i.e., the previous script is run. You may change the number of threads (`--threads=N`), the load (use `--rate=N`, see `sysbench --help`) and running time (`--time=360`).

Afterwards, the script reports the statistics, including the queries mix, the transactions throughput, and the latency (average and 95th tail):

```
SQL statistics:
    queries performed:
        read:                            3422483
        write:                           3551947
        other:                           563730
        total:                           7538160
    transactions:                        252068 (700.15 per sec.)
    queries:                             7538160 (20938.04 per sec.)
    ignored errors:                      30904  (85.84 per sec.)
    reconnects:                          0      (0.00 per sec.)

General statistics:
    total time:                          360.0202s
    total number of events:              252068

Latency (ms):
         min:                                    0.16
         avg:                                   11.42
         max:                                 1965.81
         95th percentile:                       24.83
         sum:                              2879436.25

Threads fairness:
    events (avg/stddev):           31508.5000/70.33
    execution time (avg/stddev):   359.9295/0.01
```


[sysbench]: https://github.com/akopytov/sysbench
[sysbench-tpcc]: https://github.com/Percona-Lab/sysbench-tpcc
[difference-tpcc]: https://www.percona.com/blog/tpcc-like-workload-sysbench-1-0/