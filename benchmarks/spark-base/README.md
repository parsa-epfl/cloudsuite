Spark Base Image for Cloudsuite
==========

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This repository contains the docker image with a base Spark image for the CloudSuite workloads.

## Building the images ##

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:
- [`spark-master`][sparkmasterdocker] This builds an image with the Spark master node.
- [`spark-worker`][sparkworkerdocker] This builds an image with the Spark worker node. You may spawn clusters of several workers.
- [`spark-client`][sparkclientdocker] This builds an image with the Spark client node. The client is used to start the benchmark.

These images are automatically built using the mentioned Dockerfiles available on [`ParsaLab/cloudsuite`][repo].

### Starting the volume images ###

In order for these images to work properly, you will need to create a `data` and a `bench` volume container.

The `data` container contains the dataset that is necessary for the benchmark to run. The volume creates the `/data` folder in the Spark images, from where the dataset can be accessed by the benchmark.

The `bench` container hosts the Java Spark binaries and scripts necessary to run the benchmark. The client `Entrypoint` script looks for a folder with the same name as the command line argument passed to the `docker run` command and runs the `run_benchmark.sh` script in that folder.

Assuming all the volume images are pulled, the following command will start the volume images, making both the data and the binaries available for other docker images in the host:

    $ docker create --name data [data-volume-image-tag]
    $ docker create --name bench [binary-volume-image-tag]

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

    $ docker run --rm --volumes-from data --volumes-from bench --link spark-master spark-client [benchmark-name]

The following command will start the client in interactive mode:

    $ docker run --rm --volumes-from data --volumes-from bench --link spark-master -it spark-client bash

To run the benchmark from the interactive container, use the following command:

    $ bash /benchmark/[benchmark-name]/run_benchmark.sh

    [sparkmasterdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/spark-base/spark-master/Dockerfile "Spark Master Node Dockerfile"
    [sparkworkerdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/spark-base/spark-worker/Dockerfile "Spark Worker Dockerfile"
    [sparkclientdocker]: https://github.com/ParsaLab/cloudsuite/blob/master/benchmarks/spark-base/spark-client/Dockerfile "Spark Client Dockerfile"

    [repo]: https://github.com/ParsaLab/cloudsuite/ "GitHub Repo"

    [dhrepo]: https://hub.docker.com/r/cloudsuite/spark-base/ "DockerHub Page"
    [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/spark-base.svg "Go to DockerHub Page"
    [dhstars]: https://img.shields.io/docker/stars/cloudsuite/spark-base.svg "Go to DockerHub Page"
