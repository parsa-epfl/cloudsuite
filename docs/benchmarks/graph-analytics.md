# Graph Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This repository contains the docker image for Cloudsuite's Graph Analytics benchmark.

The Graph Analytics benchmark relies the Spark framework to perform graph analytics on large-scale datasets. Apache provides a graph processing library, GraphX, designed to run on top of Spark. The benchmark performs PageRank on a Twitter dataset.

### Getting the Image

Current version of the benchmark is 3.0. To obtain the image:

    $ docker pull cloudsuite/graph-analytics

### Datasets

The benchmark uses a graph dataset generated from Twitter. To get the dataset image:

    $ docker pull cloudsuite/twitter-dataset-graph
    $ docker create --name twitter-data cloudsuite/twitter-dataset-graph

More information about the dataset is available at
[cloudsuite/twitter-dataset-graph][ml-dhrepo].

### Running/Tweaking the Benchmark

The benchmark runs the PageRank algorithm on GraphX through the spark-submit script distributed with Spark. Any arguments are passed to spark-submit.

For example, to ensure that Spark has enough memory allocated to be able to execute the benchmark in-memory, supply it with --driver-memory and  --executor-memory arguments:

    $ docker run --rm --volumes-from twitter-data cloudsuite/graph-analytics \
                 --driver-memory 4g --executor-memory 4g

### Multi-node deployment

This section explains how to run the benchmark using multiple Spark
workers (each running in a Docker container) that can be spread across
multiple nodes in a cluster. For more information on running Spark
with Docker look at [cloudsuite/spark:2.3.1][spark-dhrepo].

First, create a dataset image on every physical node where Spark
workers will be running.

    $ docker create --name twitter-data cloudsuite/twitter-dataset-graph

Start Spark master and Spark workers. They should all run within the same Docker network, which we call spark-net here. The workers get access to the datasets with --volumes-from twitter-data.

    $ docker run -dP --net host --name spark-master \
                 cloudsuite/spark:2.3.1 master
    $ docker run -dP --net host --volumes-from twitter-data --name spark-worker-01 \
                 cloudsuite/spark:2.3.1 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ docker run -dP --net host --volumes-from twitter-data --name spark-worker-02 \
                 cloudsuite/spark:2.3.1 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ ...

Finally, run the benchmark as the client to the Spark master:

    $ docker run --rm --net host --volumes-from twitter-data \
                 cloudsuite/graph-analytics \
                 --driver-memory 4g --executor-memory 4g \
                 --master spark://SPARK-MASTER-IPADDRESS:7077

[dhrepo]: https://hub.docker.com/r/cloudsuite/graph-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/graph-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/graph-analytics.svg "Go to DockerHub Page"
[ml-dhrepo]: https://hub.docker.com/r/cloudsuite/twitter-dataset-graph/
[spark-dhrepo]: https://hub.docker.com/r/cloudsuite/spark/
