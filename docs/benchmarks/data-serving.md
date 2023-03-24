# Data Serving

[![Pulls on DockerHub][dhpulls]][dhrepo] [![Stars on DockerHub][dhstars]][dhrepo]

The data serving benchmark relies on the Yahoo! Cloud Serving Benchmark (YCSB). YCSB is a framework to benchmark data store systems. This framework has an appropriate interface to populate and stress many popular data-serving systems. Here we provide the instructions and pointers to download and install YCSB and use it with the Cassandra data store.

### Server Container

Start the server container that will run cassandra server and installs a default keyspace usertable:

```bash
$ docker run --name cassandra-server --net host cloudsuite/data-serving:server
```

The following options can be used to modify the setting of the server:
- `--listen-ip=<u8.u8.u8.u8>`: Cassandra's listening IP address. By default, the script would automatically detect and use the active IP address for Cassandra. However, when the default setting does not work or you have multiple IP addresses, you can use this option to specify one. Please make sure this IP address is accessible by the client. 
- `--reader-count=<int>`: The number of reader threads Cassandra uses. According to Cassandra's suggestion, each disk containing the database could have 16 threads to hide its latency. The default value is 16, assuming all the data is stored on a single disk.
- `--writer-count=<int>`: The number of writer threads Cassandra uses. Cassandra recommends eight threads per CPU core. The default value is 32.
- `--heap-size=<int>`: JVM heap size. Its unit is GB, and by default, JVM uses `max(min(1/2 ram, 1GB), min(1/4 ram, 8GB))`. It is good to overload the value when the server has enough DRAM for better performance or restrict the value for explicit resource restriction.
- `--affinity=<cpu_id, ...>`: The CPU Cassandra works on. This setting is useful to set CPU affinity explicitly. Usually, it is combined with the container's resource management option (e.g., `--cpuset-cpus`). 

### Multiple Server Containers

Please note the server containers cannot be hosted on the same node when the host network configuration is used because they will all try to use the same port.

For a cluster setup with multiple servers, we need to instantiate a seed server :

```bash
$ docker run --name cassandra-server-seed --net host cloudsuite/data-serving:server
```

Then we prepare the server as previously.

The other server containers are instantiated as follows on **different VMs**:

```bash
$ docker run --name cassandra-subserver --net host cloudsuite/data-serving:server --seed-server-ip=<seed node IP address>
``` 

You may find a more detailed tutorial on checking the status and customizing the yaml file [here](https://www.digitalocean.com/community/tutorials/how-to-install-cassandra-and-run-a-multi-node-cluster-on-ubuntu-22-04).

### Client Container
Start the client container specifying server name(s), or IP address(es), separated with commas, as the last command argument:

```bash
$ docker run -it --name cassandra-client --net host cloudsuite/data-serving:client bash
```

Before running the measurement, you have to fill the server with the dataset. Use the script `warmup.sh` for a quick setting:

```bash
$ ./warmup.sh <server_ip> <record_count> <threads=1>
```

During the warmup period, the script creates a table for the seed server and populates the table with a given amount of records. Based on the definition(see setup_tables.txt) of the record, the size of each record is 1KB. As a result, a typical 10GB dataset requires 10M records. You can also increase the number of YCSB threads to improve the writing speed if the load generator becomes the bottleneck.


After the warmup is finished, you can use `load.sh` to apply load to the server, with 50% read operation and 50% update operation:

```bash
$ ./load.sh <server_ip> <record_count> <target_load> <threads=1> <operation_count=load * 60>
```

You can give your expected load, and YCSB will try to meet the requirement. The reported throughput will be smaller if the server cannot sustain the given load. You can also control the operation count to control the running time. Like the warmup stage, you can increase the YCSB thread count if the load generator is the bottleneck.

More detailed instructions on generating the dataset and load can be found in Step 5 at [this](http://github.com/brianfrankcooper/YCSB/wiki/Running-a-Workload) link. Although Step 5 in the link describes the data loading procedure, other steps (e.g., 1, 2, 3, 4) are useful for understanding the YCSB settings. In this case, our scripts (`warmup.sh` and `load.sh`) are good templates for further customization.

A rule of thumb on the dataset size
-----------------------------------
If you are only profiling CPU microarchitectures, you should ensure that the hot data part (3% ~ 5% of the dataset) cannot be buffered on-chip to mimic the realistic situation. Usually, 10GB dataset is enough for a typical CPU with less than 100MB LLC.

Tuning the server performance
-----------------------------
1. There is no fixed tail latency requirement for this workload. As a reference, the 99th tail latency should usually be around 5ms to 10ms to not delay its upstream service.
2. The server settings are generally under the $CASSANDRA_PATH/conf folder. The main file is cassandra.yaml. The file has comments about all parameters. These parameters can also be found here: http://wiki.apache.org/cassandra/StorageConfiguration
3. Make sure that half of the main memory is free for the operating system file buffers and caching. 
4. As a workload based on JVM, you need to load the server to warm up the JIT cache. You can keep monitoring the throughput and tail latency and take measurement when it becomes relatively stable. As a reference, it takes around 2 minutes for a modern x86 machine (Skylake) to attain stable throughput.
5. Additionally, the following links are useful pointers for performance tuning:

    a. http://spyced.blogspot.com/2010/01/linux-performance-basics.html

    b. http://wiki.apache.org/cassandra/MemtableThresholds

[dhrepo]: https://hub.docker.com/r/cloudsuite/data-serving/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-serving.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-serving.svg "Go to DockerHub Page"
