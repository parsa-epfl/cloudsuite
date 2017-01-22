# Data Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The explosion of accessible human-generated information necessitates automated
analytical processing to cluster, classify, and filter this information. The
MapReduce paradigm has emerged as a popular approach to handling large-scale
analysis, farming out requests to a cluster of nodes that first perform
filtering and transformation of the data (map) and then aggregate the results
(reduce). The Data Analytics benchmark is included in CloudSuite to cover the
increasing importance of machine learning tasks analyzing large amounts of data
in datacenters using the MapReduce framework. It is composed of Mahout, a set
of machine learning libraries, running on top of Hadoop, an open-source
implementation of MapReduce.

The benchmark consists of running a Naive Bayes classifier on a Wikimedia
dataset. It uses Hadoop version 2.7.3 and Mahout version 0.12.2.

## Images ##

To obtain the images:
```
$ docker pull cloudsuite/hadoop
$ docker pull cloudsuite/data-analytics
```

## Running the benchmark ##

The benchmark is designed to run on a Hadoop cluster, where the single master
runs the driver program, and the slaves run the mappers and reducers.

First, create a network to isolate your Hadoop cluster:
```
$ docker network create hadoop-net
```

Start the master with:
```
$ docker run -d --net hadoop-net --name master --hostname master cloudsuite/data-analytics master
```

Start a number of slaves with:
```
$ docker run -d --net hadoop-net --name slave01 --hostname slave01 cloudsuite/hadoop slave
$ docker run -d --net hadoop-net --name slave02 --hostname slave02 cloudsuite/hadoop slave
...
```

Note that it is important to set hostnames on docker containers, and that the
hostname should be the same as the name. If master's name/hostname isn't
"master", then it should be supplied to slaves when running the containers as
an argument after "slave".

Run the benchmark with:
```
$ docker exec master benchmark
```

[dhrepo]: https://hub.docker.com/r/cloudsuite/data-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-analytics.svg "Go to DockerHub Page"
