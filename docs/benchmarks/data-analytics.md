# Data Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The explosion of human-generated information necessitates automated analytical processing to cluster, classify, and filter this information.The Data Analytics benchmark is included in CloudSuite to cover the increasing importance of classification tasks in analyzing large amounts of data in datacenters. It uses the MapReduce framework Hadoop, which is a popular approach for handling large-scale analysis. Its distributed file system and compute capabilities allow it to scale to PetaBytes of data. 

This workload is based on Mahout, a set of machine learning libraries running on top of Hadoop. It runs a Naive Bayes classifier on a [Wikimedia dataset](https://dumps.wikimedia.org/backup-index.html), and uses Hadoop version 2.10.2 and Mahout version 14.1.


## Dockerfiles

Supported tags and their respective `Dockerfile` tags:
- [`latest`][latestcontainer] contains the application logic.

## Running the benchmark ##

The benchmark is designed to run on a Hadoop cluster, where a single master runs the driver program, and workers run the mappers and reducers.

First, start the container for the dataset:

```bash
$ docker create --name wikimedia-dataset cloudsuite/wikimedia-pages-dataset 
```

Start the master with:

```bash
$ docker run -d --net host --volumes-from wikimedia-dataset --name data-master cloudsuite/data-analytics --master
```

By default, the Hadoop master node is listening on the first interface accessing the network. You can overwrite the listening address by adding `--master-ip=X.X.X.X`.

Start any number of Hadoop workers with:

```bash
$ # on VM1
$ docker run -d --net host --name data-slave01 cloudsuite/data-analytics --slave --master-ip=<IP_ADDRESS_MASTER>

$ # on VM2
$ docker run -d --net host --name data-slave02 cloudsuite/data-analytics --slave --master-ip=<IP_ADDRESS_MASTER>

...
```

**Note**: You should set `IP_ADDRESS_MASTER` to the master's IP address and make sure that address is accessible from each worker.

After both master and worker are set up (you can use `docker logs` to observe if the log is still being updated), run the benchmark with the following command:

```bash
$ docker exec data-master benchmark
```

### Configuring Hadoop parameters ###

A few parameters for Hadoop can be configured depending on requirements.

Hadoop infers the number of workers based on how many partitions it created with HDFS (HaDoop File System, a distributed file system for handing out dataset chunks to workers). You can increase or reduce the HDFS partition size to `N` MB with `--hdfs-block-size=N`, with 128MB being the default. The default dataset weighs 900MB. Thus, depending on the benchmark phase (sequencing, vectorization, pre-training, training, and inference), the default option `--hdfs-block-size=128` results in a split between 1 and 8 parts.

Hadoop relies on [YARN][yarn] (Yet Another Resource Negotiator) to manage its resources, and the maximum number of workers is configured by `--yarn-cores=C`, whose default value is 8. If there are more blocks than the number of workers, YARN will only allow up to `C` worker threads to process them. Please note that **at least two cores** should be given in total: One core for the map operation and another for the reduce operation. Otherwise, the process can get stuck. 

The maximum memory used by each worker is configured by `--mapreduce-mem=N`, and the default value is 2096MB. Note that depending on the number of `--yarn-cores=C`, the total physical memory required will be at least `C*N`. To avoid out-of-memory errors, we recommend allocating at least 8GB of memory (even for a single worker with two cores) in total.

To increase the number of workers, please use a bigger dataset from Wikimedia. Using partition sizes smaller than 128MB can increase the number of workers but slow down the execution due to overheads of the small partition size. 


[dhrepo]: https://hub.docker.com/r/cloudsuite/data-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-analytics.svg "Go to DockerHub Page"
[yarn]: https://hadoop.apache.org/docs/stable/hadoop-yarn/hadoop-yarn-site/YARN.html "YARN explanation"

[latestcontainer]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/data-analytics/latest/Dockerfile "link to container, github"