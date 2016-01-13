# Data Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The explosion of accessible human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. The MapReduce paradigm has emerged as a popular approach to handling large-scale analysis, farming out requests to a cluster of nodes that first perform filtering and transformation of the data (map) and then aggregate the results (reduce). The Data Analytics benchmark is included in CloudSuite to cover the increasing importance of machine learning tasks analyzing large amounts of data in datacenters using the MapReduce framework. It is composed of Mahout, a set of machine learning libraries, running on top of Hadoop, an open-source implementation of MapReduce.


### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`Base`][basedocker]: This image contains the hadoop base which is needed for both master and slave images.
 - [`Master`][masterdocker]: This image contains the main benchmark (hadoop master node, and mahout).
 - [`Slave`][slavedocker]: This image contains the hadoop slave image. 
 - [`Data`][datasetdocker]: This image contains the dataset used by the benchmark.

These images are automatically built using the mentioned Dockerfiles available on `cloudsuite/benchmarks/data-analytics/` [GitHub repo][repo].

## Starting the volume image ##
This benchmark uses a Wikipedia dataset of ~30GB. We prepared a dataset image, to download this dataset once, and use it to run the benchmark. You can pull this image from Docker Hub.

    $ docker pull cloudsuite/dataanalytics/dataset

The following command will start the volume image, making the data available for other docker images on the host:

    $ docker create --name data cloudsuite/dataanalytics/dataset)

## Starting the Master ##
To start the master you first have to `pull` the master image.

    $ docker pull cloudsuite/dataanalytics/master

Then, run the benchmark with the following command:

    $ docker run -d -t --dns 127.0.0.1 -P --name master -h master.cloudsuite.com --volumes-from data cloudsuite/dataanalytics/master


## Starting the Slaves ##
If you want to have a single-node cluster, please skip this step.

To have more than one node, you need to start the slave containers. In order to do that, you first need to `pull` the slave image.

    $ docker pull cloudsuite/dataanalytics/slave

To connect the slave containers to the master, you need the master IP.

    $ FIRST_IP=$(docker inspect --format="{{.NetworkSettings.IPAddress}}" master)

Then, run as many containers as you want:

    $ docker run -d -t --dns 127.0.0.1 -P --name slave$i -h slave$i.cloudsuite.com -e JOIN_IP=$FIRST_IP cloudsuite/dataanalytics/slave

Where `$i` is the slave number, you should start with 1 (i.e., slave1, slave1.cloudsuite.com, slave2, slave2.cloudsuite.com, ...). 


## Running the benchmark ##

To run the benchmark you need to go to the master container.

    $ docker exec -it master bash

Then, run the benchmark with the following command:

    $ ./run.sh

It asks you to enter the number of slaves, if you have a single-node cluster, please enter 0. 
After entering the slave number, it prepares hadoop and runs the benchmark. After the benchmark finishes, the model will be available in HDFS, under the wikipediamodel folder.

[basedocker]: https://github.com/CloudSuite-EPFL/DataAnalytics/blob/master/Dockerfile "Base Dockerfile"
[masterdocker]: https://github.com/CloudSuite-EPFL/DataAnalytics/blob/master/Dockerfile "Master Dockerfile"
[slavedocker]: https://github.com/CloudSuite-EPFL/DataAnalytics/blob/master/Dockerfile "Slave Dockerfile"

[datasetdocker]: https://github.com/CloudSuite-EPFL/DataAnalytics/blob/master/dataset/Dockerfile "Dataset Dockerfile"

[repo]: https://github.com/ParsaLab/cloudsuite/tree/master/benchmarks/data-analytics "GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/dataanalytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/dataanalytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/dataanalytics.svg "Go to DockerHub Page"
