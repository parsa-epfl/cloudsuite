# Data Serving

[![Pulls on DockerHub][dhpulls]][dhrepo] [![Stars on DockerHub][dhstars]][dhrepo]

The Data Serving benchmark relies on the Yahoo! Cloud Serving Benchmark (YCSB). YCSB is a framework to benchmark data storage systems. This framework has an appropriate interface to populate and stress many popular database management systems. This benchmark loads one of the most popular NoSQL databases: Cassandra, with YCSB to mimic a representative NoSQL database state in the cloud.
### Dockerfiles

Supported tags and their respective `Dockerfile` links:
 - [`server`][serverdocker] contains Cassandra and the script to initialize its configuration.
 - [`client`][clientdocker] contains the YCSB load generator.

### Server Container

Start the server container that will run a Cassandra server:

```bash
$ docker run --name cassandra-server --net host cloudsuite/data-serving:server
```

The following options can modify the settings of the server:
- `--listen-ip=<u8.u8.u8.u8>`: Cassandra's listening IP address. By default, the script will automatically detect and use the active IP address for Cassandra. However, when the default setting does not work or you have multiple IP addresses, you can use this option to specify one. Please make sure this IP address is accessible by the client. 
- `--reader-count=<int>`: The number of reader threads Cassandra uses. According to Cassandra's suggestions, each disk containing the database should have 16 threads to hide its latency. The default value is 16, assuming all the data is stored on a single disk.
- `--writer-count=<int>`: The number of writer threads Cassandra uses. Cassandra recommends 8 threads per CPU core. The default value is 32.
- `--heap-size=<int>`: JVM heap size. Its unit is GB, and by default, JVM uses `max(min(1/2 ram, 1GB), min(1/4 ram, 8GB))`. It is good to increase the value when the server has enough DRAM for better performance or lower the value for explicit resource restriction.
- `--affinity=<cpu_id, ...>`: The CPUs Cassandra works on. This setting let Cassandra be aware of its CPU affinity explicitly. It should be used together with the container's resource management option (e.g., `--cpuset-cpus`). 
- `--row-cache=<size>`: The size of the row cache. Also specify the unit, for example `16GiB` or `256MiB`. By default, the row cache is disabled.


### Multiple Server Containers

Please note  server containers cannot be hosted on the same node when the host network configuration is used, because they all use the same port.

For a cluster setup with multiple servers, we need to instantiate a seed server :

```bash
$ docker run --name cassandra-server-seed --net host cloudsuite/data-serving:server
```

The other server containers are instantiated as follows on **different VMs**:

```bash
$ docker run --name cassandra-subserver --net host cloudsuite/data-serving:server --seed-server-ip=<seed node IP address>
``` 

You may find a more detailed tutorial on checking the status and customizing the yaml file [here](https://www.digitalocean.com/community/tutorials/how-to-install-cassandra-and-run-a-multi-node-cluster-on-ubuntu-22-04).

### Client Container
Start the client container with bash:

```bash
$ docker run -it --name cassandra-client --net host cloudsuite/data-serving:client bash
```

Before running the measurement, you have to fill the server with the dataset. Use the script `warmup.sh`:

```bash
$ ./warmup.sh <server_ip> <record_count> <threads=1>
```

During the warm-up period, the script creates a table for the seed server and populates it with a given number of records. Based on the definition (see `setup_tables.txt`) of the record, the size of each record is 1KB. As a result, a typical 10GB dataset requires 10M records. You can also increase the number of YCSB threads to improve the writing speed if the load generator becomes the bottleneck.


After the warm-up is finished, you can use `load.sh` to apply load to the server, with 50% read and 50% update operations:

```bash
$ ./load.sh <server_ip> <record_count> <target_load> <threads=1> <operation_count=load * 60>
```

You can give your expected load, and YCSB will try to meet the requirement. The reported throughput will be smaller if the server cannot sustain the given load. You can also control the total run time by changing `operation_count`. Like the warm-up stage, you can increase the YCSB thread count if the load generator is the bottleneck.

More detailed instructions on generating the dataset and load can be found in Step 5 at [this](http://github.com/brianfrankcooper/YCSB/wiki/Running-a-Workload) link. Although Step 5 in the link describes the data loading procedure, other steps (e.g., 1, 2, 3, 4) are useful for understanding the YCSB settings. In this case, our scripts (`warmup.sh` and `load.sh`) are good templates for further customization.

There are a couple of pre-defined workloads from YCSB. For example, Workload C has 100% read operations with no write operations. The default workload is Workload A (50% read + 50% write). You can change the workload by modifying the `load.sh` script. The other workloads can be found at [this](https://github.com/brianfrankcooper/YCSB/wiki/Core-Workloads) link.

A rule of thumb on the dataset size
-----------------------------------
If you are only profiling CPU microarchitectures, you should ensure that the hot data part (3% ~ 5% of the dataset) cannot be buffered on-chip to mimic a realistic situation. Usually, a 10GB dataset is enough for a typical CPU with less than 50MB LLC.

Tuning the server performance
-----------------------------
1. There is no fixed tail latency requirement for this workload. As a reference, the 99 percentile latency should usually be around 5ms to 10ms to not delay its upstream service.
2. The server settings are under the $CASSANDRA_PATH/conf folder. The main file is cassandra.yaml. The file has comments about all parameters. These parameters can also be found here: http://wiki.apache.org/cassandra/StorageConfiguration
3. Make sure that half of the main memory is free for the operating system file buffers and caching. 
4. As a workload based on JVM, you need to load the server to warm up the JIT cache. You can keep monitoring the throughput and tail latency and take measurement when it becomes relatively stable. As a reference, it takes around 2 minutes for a modern x86 machine (Skylake) to attain stable throughput (5000 RPS, 50% read and 50% update).
5. The server has row cache disabled by default. It is used to cache the data rows in memory. It is useful for read-intensive workloads (e.g., Workload B and Workload C) and can improve throughput. However, it is not recommended for write-intensive workloads as it trashes the cache frequently. Tune it according to your workload.
6. The following links are useful pointers for performance tuning:

    a. http://spyced.blogspot.com/2010/01/linux-performance-basics.html

    b. http://wiki.apache.org/cassandra/MemtableThresholds

[dhrepo]: https://hub.docker.com/r/cloudsuite/data-serving/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-serving.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-serving.svg "Go to DockerHub Page"

[serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/data-serving/server/Dockerfile "Server Dockerfile"

[clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/data-serving/client/Dockerfile "Client Dockerfile"