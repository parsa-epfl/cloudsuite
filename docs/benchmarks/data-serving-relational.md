# Data Serving (PostgreSQL)

The Data Serving benchmark (based on PostgreSQL 15) uses [sysbench][sysbench] and [sysbench-tpcc][sysbench-tpcc] as the load generator. With several different [aspects][difference-tpcc] from the standard TPC-C, it can still reflect most of the important properties of the TPC-C workload. One of the most widely used workloads from sysbench (`oltp_read_write`) is also wrapped for measurement.

### Dockerfiles
Supported tags and their respective `Dockerfile` links:
- `server` contains PostgreSQL 15, and by default it opens the prompt using user `postgres`.
- `client` contains sysbench, sysbench-tpcc, and template load generation script to run the workload.

### Server Container

Start the server container with the following command:

```bash

$ docker run --name postgresql-server -it --net host cloudsuite/data-serving-relational:server

```

It creates a database user `cloudsuite` (password is `cloudsuite` as well), a database `sbtest`, and grant database's permission to the user. The user has the permission to access the database remotely. 


### Client Container

We have two types of benchmarks, TPC-C and Sysbench standard OLTP read/write workload. Both of them require you to point to the destination server with `--server-ip=<IP>`. To run the warmup phase one can pass the `--warmup` argument, and for the actual measurements `--run`.

Depending on which one you want to launch, you can pick between `--tpcc` and `--oltp-rw` such as the following:

```bash
docker run --name sysbench-client -it --net host cloudsuite/data-serving-relational:client --warmup <--tpcc | --oltp-rw> --server-ip=127.0.0.1
```

And for running the benchmark you can run the following command: 

```bash
docker run --name sysbench-client -it --net host cloudsuite/data-serving-relational:client --run <--tpcc | --oltp-rw> --server-ip=127.0.0.1
```

#### TPC-C

For the TPC-C benchmark we can control the following arguments:
- `--threads=N` spawns `N` threads for the load generator, default is 8 threads.
- `--report-interval=s` report the intermediate statistics every `s` seconds, default is 10 seconds.
- `--time=s` the length in `s` seconds of the benchmark, default is 360 seconds.
- `--scale=N` the scale `N` of the database, default is 50 times.
- `--rate=N` the expected load (transaction per second), and the default is omitted, which means pushing to the maximum possible throughput the server could sustain.

#### Sysbench OLTP Read/write Workload

For the Sysbench OLTP read/write workload, you can configure the following parameters:
- `--threads=N` spawns `N` threads for the load generator.
- `--report-interval=s` report the intermediate statistics every `s` seconds.
- `--time=s` the length in `s` seconds of the benchmark.
- `--scale=N` the scale `N` of the database.
- `--rate=N` the expected load (transaction per second), and the default is omitted, which means pushing to the maximum possible throughput the server could sustain.

```bash
$ docker run --name sysbench-client -it --net host cloudsuite/data-serving-relational:client
```

### Container

You can enter the container with the following command:

```bash
$ docker run --name sysbench-client -it --net host --entrypoint bash cloudsuite/data-serving-relational:client
```

- `/root/template/database.conf` defines the port to the PostgreSQL. You can modify the IP address and the port accordingly based on the configuration of your server container.
- More options can be added by referring `sysbench --help` and the help of each workload.

### Results

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