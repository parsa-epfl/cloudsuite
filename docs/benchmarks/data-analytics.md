# Data Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The explosion of accessible human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. The MapReduce paradigm has emerged as a popular approach to handling large-scale analysis, farming out requests to a cluster of nodes that first perform filtering and transformation of the data (map) and then aggregate the results (reduce). The Data Analytics benchmark is included in CloudSuite to cover the increasing importance of machine learning tasks analyzing large amounts of data in datacenters using the MapReduce framework. It is composed of Mahout, a set of machine learning libraries, running on top of Hadoop, an open-source implementation of MapReduce.

The benchmark consists of running a Naive Bayes classifier on a Wikimedia dataset. It uses Hadoop version 2.10.1 and Mahout version 0.13.0.

## Images ##

To obtain the images:

```bash
$ docker pull cloudsuite/hadoop:2.10.1
$ docker pull cloudsuite/data-analytics:4.0

```

## Running the benchmark ##

The benchmark is designed to run on a Hadoop cluster, where the single master runs the driver program, and the slaves run the mappers and reducers.

Start the master with:

```bash
$ docker run -d --net host --name master cloudsuite/data-analytics master
```

Start any number of Hadoop slaves with:
```
$ # on VM1
$ docker run -d --net host --name slave01 cloudsuite/hadoop:2.10.1 slave $IP_ADRESS_MASTER

$ # on VM2
$ docker run -d --net host --name slave02 cloudsuite/hadoop:2.10.1 slave $IP_ADRESS_MASTER

...
```
Note : Start each slave on a different VM.

Run the benchmark with:

```bash
$ docker exec master benchmark
```

[dhrepo]: https://hub.docker.com/r/cloudsuite/data-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-analytics.svg "Go to DockerHub Page"
