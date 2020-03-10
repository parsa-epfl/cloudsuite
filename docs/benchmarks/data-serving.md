# Data Serving

[![Pulls on DockerHub][dhpulls]][dhrepo] [![Stars on DockerHub][dhstars]][dhrepo]

The data serving benchmark relies on the Yahoo! Cloud Serving Benchmark (YCSB). YCSB is a framework to benchmark data store systems. This framework comes with appropriate interfaces to populate and stress many popular data serving systems. Here we provide the instructions and pointers to download and install YCSB and use it with the Cassandra data store.

## Generating Datasets

The YCSB client has a data generator. After starting Cassandra, YCSB can start loading the data. First, you need to create a keyspace named *usertable* and a column family for YCSB. This is a must for YCSB to load data and run.



### Server Container
Start the server container that will run cassandra server and installs a default keyspace usertable:

```bash
$ docker run --name cassandra-server --net host cloudsuite/data-serving:server
```
### Multiple Server Containers

For a cluster setup with multiple servers, we need to instantiate a seed server :

```bash
$ docker run --name cassandra-server-seed --net host cloudsuite/data-serving:server
```

Then we prepare the server as previously.

The other server containers are instantiated as follows on different VMs:

```bash
$ docker run --name cassandra-server(id) --net host -e CASSANDRA_SEEDS=cassandra-server-seed-IPADDRESS cloudsuite/data-serving:server
```

You can find more details at the websites: http://wiki.apache.org/cassandra/GettingStarted and https://hub.docker.com/_/cassandra/.

Make sure all non-seed servers are stablished (adding them concurrently may lead to a [problem](https://docs.datastax.com/en/cassandra/2.1/cassandra/operations/ops_add_node_to_cluster_t.html)).

### Client Container
After successfully creating the aforementioned schema, you are ready to benchmark with YCSB.
Start the client container specifying server name(s), or IP address(es), separated with commas, as the last command argument:

```bash
$ docker run --name cassandra-client --net host cloudsuite/data-serving:client "cassandra-server-seed-IPADDRESS,cassandra-server1-IPADDRESS"
```

More detailed instructions on generating the dataset can be found in Step 5 at [this](http://github.com/brianfrankcooper/YCSB/wiki/Running-a-Workload) link. Although Step 5 in the link describes the data loading procedure, other steps (e.g., 1, 2, 3, 4) are very useful to understand the YCSB settings.

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

Running the benchmark
---------------------
The benchmark is run automatically with the client container. One can modify the record count in the database and/or the number of operations performed by the benchmark specifying the corresponding variables when running the client container:
```bash
$ docker run -e RECORDCOUNT=<#> -e OPERATIONCOUNT=<#> --name cassandra-client --net host cloudsuite/data-serving:client "cassandra-server-seed,cassandra-server1"
```

[dhrepo]: https://hub.docker.com/r/cloudsuite/data-serving/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-serving.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-serving.svg "Go to DockerHub Page"
