# Data Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The explosion of accessible human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. Hadoop has emerged as a popular approach to handling large-scale analysis with its distributed file system and compute capabilities, allowing it to scale to PetaBytes of data. The Data Analytics benchmark is included in CloudSuite to cover the increasing importance of classification tasks analyzing large amounts of data in datacenters using the MapReduce framework. It is composed of Mahout, a set of machine learning libraries, running on top of Hadoop, an open-source implementation of MapReduce.

The benchmark consists of running a Naive Bayes classifier on a (Wikimedia dataset)[https://dumps.wikimedia.org/backup-index.html]. It uses Hadoop version 2.10.2 and Mahout version 14.1.

## Images ##

To obtain the images:

```bash
$ docker pull cloudsuite/data-analytics
$ docker pull cloudsuite/wikimedia-pages-dataset
```

## Running the benchmark ##

The benchmark is designed to run on a Hadoop cluster, where the single master runs the driver program, and the slaves run the mappers and reducers.

First, start the container for the dataset:

```bash
$ docker create --name wikimedia-dataset cloudsuite/wikimedia-pages-dataset 
```

**Note**: The following commands will start the master for the cluster. To make sure that slaves and master can communicate with each other, the slave container's must point to the master's IP address. 

Start the master with:

```bash
$ docker run -d --net host --volumes-from wikimedia-dataset --name data-master cloudsuite/data-analytics --master
```

Start any number of Hadoop slaves with:
```
$ # on VM1
$ docker run -d --net host --name data-slave01 cloudsuite/data-analytics --slave --master-ip=<IP_ADDRESS_MASTER>

$ # on VM2
$ docker run -d --net host --name data-slave02 cloudsuite/data-analytics --slave --master-ip=<IP_ADDRESS_MASTER>

...
```
**Note**: You should set `IP_ADDRESS_MASTER` to master's IP address.

Run the benchmark with:

```bash
$ docker exec data-master benchmark
```

### Configuring Hadoop parameters ###

We can configure a few parameters for Hadoop depending on requirements. 

Hadoop infers the number of workers with how many partitions it created with HDFS. We can increase or reduce the HDFS partition size to `N` mb with `--hdfs-block-size=N`, 128mb being the default. The current dataset included here weights 900MB, thus the default `--hdfs-block-size=128` of 128mb resulting in splits between 1 and 8 parts depending on the benchmark phase.

The maximum number of workers is configured by `--yarn-cores=C`, default is 8, if there's more splits than number of workers, YARN will only allow up to `C` workers threads to process them and multiplex the tasks. Please note that **at least 2 cores** should be given for all workers in total: One core for the map operation and another core for the reduce operation. Otherwise, the process can get stuck. 

The maximum memory used by each worker is configured by `--mapreduce-mem=N`, default is 2096mb. Note that depending on the number of `--yarn-cores=C`, the total actual physical memory required will be of at least `C*N`. You are recommended to allocate 8GB memory (even for single worker with 2 CPUs) in total to avoid out of memory errors.

For increasing total number of workers, please use a bigger dataset from wikimedia. Using a smaller partition sizes than 128 mb will result in increasing number of workers but also will actually slowdown the execution due to overheads of small partition size. 


[dhrepo]: https://hub.docker.com/r/cloudsuite/data-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-analytics.svg "Go to DockerHub Page"
