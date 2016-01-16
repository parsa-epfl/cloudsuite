CoudSuite Graph Analytics Benchmark
==========
This repository contains the docker image for Cloudsuite's Graph Analytics benchmark.

The Graph Analytics benchmark relies the Spark framework to perform graph analytics on large-scale datasets. Apache provides a graph processing library, GraphX, designed to run on top of Spark. The benchmark performs PageRank on a Twitter dataset.

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

## Using the benchmark ##

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

- [`benchmark`][benchmarkdocker] This builds a volume image that contains the benchmark's binaries.
- [`data`][datadocker] This builds a volume image with the benchmark's dataset.
- [`spark-master`][sparkmasterdocker] This builds an image for the Spark master node.
- [`spark-worker`][sparkworkerdocker] This builds an image for the Spark worker node. You may spawn several workers.
- [`spark-client`][sparkclientdocker] This builds an image with the Spark client node. The client is used to start the benchmark.

These images are automatically built using the mentioned Dockerfiles available on [`ParsaLab/cloudsuite`][repo].

### Starting the volume images ###

The first step is to create the volume images that contain the binaries and the dataset of the Graph Analytics benchmark. First `pull` the volume images, using the following command:

    $ docker pull cloudsuite/graph-analytics:data
    $ docker pull cloudsuite/graph-analytics:benchmark

The following command will start the volume images, making both the data and the binaries available for other docker images on the host:

    $ docker create --name data cloudsuite/graph-analytics:data
    $ docker create --name bench cloudsuite/graph-analytics:benchmark

### Starting the master node ###

To start the server you have to first `pull` the Spark master node image and then run it. To `pull` the Spark master node image, use the following command:

    $ docker pull cloudsuite/spark-base:master

The following command will start the master node and forward port 8080 to the host, so that the Spark web interface can be accessed from the web browser, using the host IP address.

    $ docker run -d -P -p 8080:8008-h master --volumes-from data --volumes-from bench --name spark-master cloudsuite/spark-base:master

### Starting the workers ###

To start a worker you have to first `pull` the Spark worker node image and then run it. To `pull` the Spark worker node image, use the following command:

    $ docker pull cloudsuite/spark-base:worker

The following command will start the worker node.

    $ docker run -d -P --volumes-from data --volumes-from bench --link spark-master --name spark-worker1 cloudsuite/spark-base:worker spark://master:7077

### Starting the client and running the benchmark ###

To start the client you have to first `pull` the Spark client node image and then run it. To `pull` the Spark client node image, use the following command:

    $ docker pull cloudsuite/spark-base:client

The following command will start the client node and run the benchmark:

    $ docker run --rm --volumes-from data --volumes-from bench --link spark-master spark-client graph_analytics

The following command will start the client in interactive mode:

    $ docker run --rm --volumes-from data --volumes-from bench --link spark-master -it spark-client bash

To run the benchmark from the interactive container, use the following command:

    $ bash /benchmark/graph_analytics/run_benchmark.sh

[benchmarkdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/graph-analytics/benchmark/Dockerfile "Benchmark volume Dockerfile"
[datadocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/graph-analytics/data/Dockerfile "Data volume Dockerfile"
[sparkmasterdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/spark-base/spark-master/Dockerfile "Spark Master Node Dockerfile"
[sparkworkerdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/spark-base/spark-worker/Dockerfile "Spark Worker Dockerfile"
[sparkclientdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/spark-base/spark-client/Dockerfile "Spark Client Dockerfile"
[repo]: https://github.com/ParsaLab/cloudsuite "GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/graph-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/graph-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/graph-analytics.svg "Go to DockerHub Page"

[serverdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/data-caching/server/Dockerfile "Server Dockerfile"

[clientdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/data-caching/client/Dockerfile "Client Dockerfile"
