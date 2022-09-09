# Graph Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This repository contains the docker image for Cloudsuite's Graph Analytics benchmark.

The Graph Analytics benchmark relies the Spark framework to perform graph analytics on large-scale datasets. Apache provides a graph processing library, GraphX, designed to run on top of Spark. The benchmark performs PageRank on a Twitter dataset.

### Getting the Image

Current version of the benchmark is 4.0. To obtain the image:

    $ docker pull cloudsuite/graph-analytics:4.0

### Datasets

The benchmark uses a graph dataset generated from Twitter. To get the dataset image:

    $ docker pull cloudsuite/twitter-dataset-graph:4.0
    $ docker create --name twitter-data cloudsuite/twitter-dataset-graph:4.0

More information about the dataset is available at
[cloudsuite/twitter-dataset-graph][ml-dhrepo].

### Running/Tweaking the Benchmark

The benchmark can run three graph algorithms using GraphX through the spark-submit script distributed with Spark. The algorithms are page rank, connected components, and triangle count.

To run the benchmark, run the following command:

    $ docker run --rm --volumes-from twitter-data -e WORKLOAD_NAME=pr cloudsuite/graph-analytics:4.0 \
                 --driver-memory 4g --executor-memory 4g

Note that any argument passed to the container will be directed to spark-submit. In the given command, to ensure that Spark has enough memory allocated to be able to execute the benchmark in-memory, --driver-memory and --executor-memory arguments are passed to spark-submit. Adjust the spark-submit arguments based on the chosen algorithm and your system and container's configurations.

The environment variable `WORKLOAD_NAME` sets the graph algorithm that the container executes. Use `pr`, `cc`, and `tc` for page rank, connected components, and triangle count, respectively. 

All these analytics require huge memory to finish. As ar reference, running `tc` with single CPU core requires both 8GB driver-memory and executor-memory. If you allocate more cores, more memory is necessary. You will see the `OutOfMemoryError` exception if you do not allocate enough memory. 

### Multi-node deployment

This section explains how to run the benchmark using multiple Spark
workers (each running in a Docker container) that can be spread across
multiple nodes in a cluster. For more information on running Spark
with Docker look at [cloudsuite/spark:2.4.5][spark-dhrepo].

**Note**: The following commands will run the Spark cluster within host's network. To make sure that slaves and master can communicate with each other, the master container's hostname, which should be host's hostname, must be able to be resolved to the same IP address by the master container and all slave containers. 

First, create a dataset image on every physical node where Spark
workers will be running.

    $ docker create --name twitter-data cloudsuite/twitter-dataset-graph:4.0

Start Spark master and Spark workers. They should all run within the same Docker network, which we call spark-net here. The workers get access to the datasets with --volumes-from twitter-data.

    $ docker run -dP --net host --name spark-master \
                 cloudsuite/spark:2.4.5 master
    $ docker run -dP --net host --volumes-from twitter-data --name spark-worker-01 \
                 cloudsuite/spark:2.4.5 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ docker run -dP --net host --volumes-from twitter-data --name spark-worker-02 \
                 cloudsuite/spark:2.4.5 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ ...

Finally, run the benchmark as the client to the Spark master:

    $ docker run --rm --net host --volumes-from twitter-data -e WORKLOAD_NAME=pagerank \
                 cloudsuite/graph-analytics:4.0 \
                 --driver-memory 4g --executor-memory 4g \
                 --master spark://SPARK-MASTER-IPADDRESS:7077

[dhrepo]: https://hub.docker.com/r/cloudsuite/graph-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/graph-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/graph-analytics.svg "Go to DockerHub Page"
[ml-dhrepo]: https://hub.docker.com/r/cloudsuite/twitter-dataset-graph/
[spark-dhrepo]: https://hub.docker.com/r/cloudsuite/spark/
