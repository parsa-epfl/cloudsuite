# Data Serving

[![Pulls on DockerHub][dhpulls]][dhrepo] [![Stars on DockerHub][dhstars]][dhrepo]

The data serving benchmark relies on the Yahoo! Cloud Serving Benchmark (YCSB). YCSB is a framework to benchmark data store systems. This framework comes with appropriate interfaces to populate and stress many popular data serving systems. Here we provide the instructions and pointers to download and install YCSB and use it with the Cassandra data store.

## Generating Datasets

The YCSB client has a data generator. After starting Cassandra, YCSB can start loading the data. First, you need to create a keyspace named *usertable* and a column family for YCSB. This is a must for YCSB to load data and run.



### Server Container

**Note**: The following commands will run the Cassandra within host's network. To make sure that slaves and master can communicate with each other, the master container's hostname, which should be host's hostname, must be able to be resolved to the same IP address by the master container and all slave containers. 

Start the server container that will run cassandra server and installs a default keyspace usertable:

```bash
$ docker run --name cassandra-server --net host cloudsuite/data-serving:server
```

The following options can be used to modify the setting of the server:
- `--listen-ip=<u8.u8.u8.u8>`: Cassandra's listening IP address. By default, the script would automatically detect the active IP address and use it for Cassandra. When the default setting does not work, or you have multiple IP addresses, you can use this option to specify one.
- `--reader-count=<int>`: The number of reader threads Cassandra uses. According to Cassandra's suggestion, each disk containing the database could have 16 threads to hide its latency. The default value is 16, assuming all the data is stored in on a single disk.
- `--writer-count=<int>`: The number of writer threads Cassandra uses. Cassandra recommends 8 thread per CPU core. The default value is 32.
- `--heap-size=<int>`: The size of JVM heap. Its unit is GB and by default, JVM uses `max(min(1/2 ram, 1GB), min(1/4 ram, 8GB))`. It is good to overload the value when the server has enough DRAM for better performance, or restrict the value for explicit resource restriction.
- `--affinity=<cpu_id, ...>`: The CPU Cassandra works on. This setting is useful to explicitly set CPU affinity. Usually, it is combined with container's resource management option (e.g., `--cpuset-cpus`). 

### Multiple Server Containers

Please note the server containers cannot be hosted on the same node when the host network configuration is used because they will all try to use the same port.

For a cluster setup with multiple servers, we need to instantiate a seed server :

```bash
$ docker run --name cassandra-server-seed --net host cloudsuite/data-serving:server
```

Then we prepare the server as previously.

The other server containers are instantiated as follows on **different VMs**:

```bash
$ docker run --name cassandra-server(id) --net host cloudsuite/data-serving:server --seed-server-ip=<seed node IP address>
```

You can find more details at the websites: http://wiki.apache.org/cassandra/GettingStarted and https://hub.docker.com/_/cassandra/.

Make sure all non-seed servers are established (adding them concurrently may lead to a [problem](https://docs.datastax.com/en/cassandra/2.1/cassandra/operations/ops_add_node_to_cluster_t.html)).

### Client Container
After successfully creating the aforementioned schema, you are ready to benchmark with YCSB.
Start the client container specifying server name(s), or IP address(es), separated with commas, as the last command argument:

```bash
$ docker run --name cassandra-client --net host cloudsuite/data-serving:client bash
```

Before running the measurement, you have to fill the server with the dataset. Use the script `warmup.sh` for a quick setting:

```bash
$ ./warmup.sh <server_ip> <record_count> <threads=1>
```

During warmup period, the script create a table to the seed server, and populate the table with given number of record. Based on the definition(see setup_tables.txt) of the record, the size of each record is 1KiB. As a result, a typical 10GiB dataset requires 10M records. You can also increase the number of YCSB threads to improve the writing speed, in case the load generator becomes the bottleneck.


After the warmup is finished, you can use `load.sh` to apply load to the server:

```bash
$ ./load.sh <server_ip> <record_count> <target_load> <threads=1> <operation_count=load * 60>
```

You can give your expected load and YCSB would try to meet the requirement. In case the server cannot sustain the given load, the reported throughput would be smaller. You can also control the operation count to control the running time. Similar to warmup stage, you can also increase the YCSB thread count if the load generator is the bottleneck.

More detailed instructions on generating the dataset and load can be found in Step 5 at [this](http://github.com/brianfrankcooper/YCSB/wiki/Running-a-Workload) link. Although Step 5 in the link describes the data loading procedure, other steps (e.g., 1, 2, 3, 4) are very useful to understand the YCSB settings. In this case, our scripts (`warmup.sh` and `load.sh`) are good template for further customization.

A rule of thumb on the dataset size
-----------------------------------
To emulate a realistic setup, you can generate more data than your main memory size if you have a low-latency, high-bandwidth I/O subsystem. For example, for a machine with 24GB memory, you can generate 30 million records corresponding to a 30GB dataset size.

_Note_: The dataset resides in Cassandra’s data folder(s).The actual data takes up more space than the total size of the records because data files have metadata structures (e.g., index). Make sure you have enough disk space.

Tuning the server performance
-----------------------------
1. In general, the server settings are under the $CASSANDRA_PATH/conf folder. The main file is cassandra.yaml. The file has comments about all parameters. This parameters can also be found here: http://wiki.apache.org/cassandra/StorageConfiguration
2. You can modify the *target* and *threadcount* variables to tune the benchmark and utilize the server. The throughput depends on the number of hard drives on the server. If there are enough disks, the cores can be utilized after running the benchmark for 10 minutes. Make sure that half of the main memory is free for the operating system file buffers and caching.
3. Additionally, the following links are useful pointers for performance tuning:

	a. http://spyced.blogspot.com/2010/01/linux-performance-basics.html

	b. http://wiki.apache.org/cassandra/MemtableThresholds


[dhrepo]: https://hub.docker.com/r/cloudsuite/data-serving/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-serving.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-serving.svg "Go to DockerHub Page"
