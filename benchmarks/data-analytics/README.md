# Data Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The explosion of accessible human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. The MapReduce paradigm has emerged as a popular approach to handling large-scale analysis, farming out requests to a cluster of nodes that first perform filtering and transformation of the data (map) and then aggregate the results (reduce). The Data Analytics benchmark is included in CloudSuite to cover the increasing importance of machine learning tasks analyzing large amounts of data in datacenters using the MapReduce framework. It is composed of Mahout, a set of machine learning libraries, running on top of Hadoop, an open-source implementation of MapReduce.


### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`Benchmark`][benchmarkdocker]: This image contains the main benchmark.
 - [`Data`][datasetdocker]: This images contains the dataset that  is used by the benchmark.

These images are automatically built using the mentioned Dockerfiles available on `CloudSuite-EPFL/DataAnalytics` [GitHub repo][repo].

## Starting the volume image ##
This benchmark uses a Wikipedia dataset of ~30GB. We prepared a dataset image, to download this dataset once, and use it to run the benchmark. You can pull this image from Docker Hub.

    $ docker pull cloudsuite/dataanalytics/dataset

The following command will start the volume image, making the data available for other docker images on the host:

    $ DATA=$(docker run -d dataset)

## Running the benchmark ##
To start the benchmark you first have to `pull` the benchmark image and then run it.

    $ docker pull cloudsuite/dataanalytics

Then, run the benchmark with the following command:

    $ docker run -it -volumes-from $DATA cloudsuite/dataanalytics /etc/bootstrap.sh -bash

Running the image automatically runs the benchmark as well. After the benchmark finishes, the model will be available in HDFS, under the wikipediamodel folder.

[benchmarkdocker]: https://github.com/CloudSuite-EPFL/DataAnalytics/blob/master/Dockerfile "Benchmark Dockerfile"

[datasetdocker]: https://github.com/CloudSuite-EPFL/DataAnalytics/blob/master/dataset/Dockerfile "Dataset Dockerfile"

[repo]: https://github.com/CloudSuite-EPFL/DataAnalytics "GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/dataanalytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/dataanalytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/dataanalytics.svg "Go to DockerHub Page"
