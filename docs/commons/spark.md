## Apache Spark

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This repository contains a Docker image of Apache Spark. Currently we support
Spark versions 1.5.1, 2.1.0 and 2.3.1. The lastest tag corresponds to version 2.3.1.

To obtain the image:

    $ docker pull cloudsuite/spark:2.3.1

## Running Spark

### Single Container

To try out Spark running in a single container, start the container with:

    $ docker run -it --rm cloudsuite/spark:2.3.1 bash

Spark installation is located under /opt/spark-1.5.1. Try running an example that
calculates Pi with 100 tasks:

    $ /opt/spark-2.3.1/bin/spark-submit --class org.apache.spark.examples.SparkPi \
        /opt/spark-2.3.1/examples/jars/spark-examples_2.11-2.3.1.jar 100

You can also run Spark programs using spark-submit without entering the
interactive shell by supplying "submit" as the command to run the image.
Arguments after "submit" are passed to spark-submit. For example, to run the
same example as above type:

    $ docker run --rm cloudsuite/spark:2.3.1 submit --class org.apache.spark.examples.SparkPi \
        /opt/spark-2.3.1/examples/jars/spark-examples_2.11-2.3.1.jar 100

Notice that the path to the jar is a path inside the container. You can pass
jars in the host filesystem as arguments if you map the directory where they
reside as a Docker volume.

Finally, you can also start an interactive Spark shell with:

    $ docker run -it --rm cloudsuite/spark:2.3.1 shell

Again, this is just a shortcut for starting a container and running
/opt/spark-2.3.1/bin/spark-shell. Try running a simple parallelized count:

    $ sc.parallelize(1 to 1000).count()

### Multi Container

Usually, we want to run Spark with multiple workers to parallelize some job. In
Docker it is typical to run a single process in a single container. Here we
show how to start a number of workers, a single Spark master that acts as a
coordinator (cluster manager), and submit a job.

Start a Spark master:

    $ docker run -dP --net host --name spark-master cloudsuite/spark:2.3.1 master

Start a number of Spark workers:

    $ docker run -dP --net host --name spark-worker-01 cloudsuite/spark:2.3.1 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ docker run -dP --net host --name spark-worker-02 cloudsuite/spark:2.3.1 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ ...

We can monitor our jobs using Spark's web UI. Point your browser to SPARK-MASTER-IPADDRESS:8080, where SPARK-MASTER-IPADDRESS is the IP of the VM/host on which spark master is running.

Finally, to submit a job, we can use any of the methods described in the Single
Container section, with the addition of the network argument to Docker and
spark-master argument to Spark.

Start Spark container with bash and run spark-submit inside it to estimate Pi:

    $ docker run -it --rm --net host:: cloudsuite/spark:2.3.1 bash
    $ /opt/spark-2.3.1/bin/spark-submit --class org.apache.spark.examples.SparkPi \
        --master spark://SPARK-MASTER-IPADDRESS:7077 \
        /opt/spark-2.3.1/examples/jars/spark-examples_2.11-2.3.1.jar 100

Start Spark container with "submit" command to estimate Pi:

    $ docker run --rm --net host cloudsuite/spark:2.3.1 submit --class org.apache.spark.examples.SparkPi \
        --master spark://SPARK-MASTER-IPADDRESS:7077 \
        /opt/spark-2.3.1/examples/jars/spark-examples_2.11-2.3.1.jar 100

Start Spark container with "shell" command and run a parallelized count:

    $ docker run -it --rm --net host cloudsuite/spark:2.3.1 shell --master spark://spark-master:7077
    $ sc.parallelize(1 to 1000).count()

[dhrepo]: https://hub.docker.com/r/cloudsuite/spark/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/spark.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/spark.svg "Go to DockerHub Page"
