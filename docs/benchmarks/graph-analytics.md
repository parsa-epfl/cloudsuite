# Graph Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The Graph Analytics benchmark relies on the Spark framework to perform graph analytics on large-scale datasets. Apache provides a graph processing library, GraphX, designed to run on top of Spark. This benchmark performs various algorithms including PageRank on a Twitter dataset.


### Dockerfiles

Supported tags and their respective `Dockerfile` tags:
- [`latest`][latestcontainer] contains the algorithm and application entrypoint.

### Datasets

The benchmark uses a graph dataset generated from Twitter. To create the dataset image:

```bash
$ docker create --name twitter-data cloudsuite/twitter-dataset-graph
```

### Running/Tweaking the Benchmark

The benchmark can run three graph algorithms using GraphX: PageRank, connected components, and triangle count.

To run the benchmark, run the following command:

```bash
$ docker run --rm --volumes-from twitter-data -e WORKLOAD_NAME=pr cloudsuite/graph-analytics \
    --driver-memory 8g --executor-memory 8g
```

Note that any arguments passed to the container will be directed to `spark-submit`, the interface to submit jobs to Spark. In the given command, to ensure that Spark has enough memory allocated to execute the benchmark in memory, `--driver-memory` and `--executor-memory` arguments are passed to `spark-submit`. 

The environment variable `WORKLOAD_NAME` sets the graph algorithm that the container executes. Use `pr`, `cc`, and `tc` for PageRank, connected components, and triangle count respectively. PageRank is selected by default if not set. 

All of these analytic workloads require huge memory to finish. As a reference, running `tc` on a single CPU core requires 8GB each. If you allocate more cores, more memory is necessary. You will see the `OutOfMemoryError` exception if you do not give enough memory. We recommend giving more than 16GB of memory for each core to minimize GC activities, which can influence your measurements. 

### Multi-node deployment

This section explains how to run the benchmark using multiple Spark workers (each running in a Docker container) that can be spread across multiple nodes in a cluster. 


First, create a dataset image on every physical node where Spark workers will be running.

```bash
$ docker create --name twitter-data cloudsuite/twitter-dataset-graph
```
Start Spark master and Spark workers. You can start the master node with the following command:

```bash
$ docker run -dP --net host --name spark-master \
    cloudsuite/spark:3.3.2 master
```

By default, the container uses the hostname as the listening IP for the connections to the worker nodes. Therefore, ensure all worker machines can access the master machine using the master hostname if the listening IP is kept by default.
You can also override the listening address by setting the environment variable `SPARK_MASTER_IP` using the container option `-e SPARK_MASTER_IP=X.X.X.X`.

The workers get access to the dataset with `--volumes-from twitter-data`.

```bash
# Set up worker 1
$ docker run -dP --net host --volumes-from twitter-data --name spark-worker-01 \
    cloudsuite/spark:3.3.2 worker spark://SPARK-MASTER:7077

# Set up worker 2
$ docker run -dP --net host --volumes-from twitter-data --name spark-worker-02 \
    cloudsuite/spark:3.3.2 worker spark://SPARK-MASTER:7077
    
# ...
```

`SPARK_MASTER` is Spark master's listening address.

Finally, run the benchmark as the client to activate the Spark master:

```bash
$ docker run --rm --net host --volumes-from twitter-data -e WORKLOAD_NAME=pr \
    cloudsuite/graph-analytics \
    --driver-memory 8g --executor-memory 8g \
    --master spark://SPARK-MASTER:7077
```


[dhrepo]: https://hub.docker.com/r/cloudsuite/graph-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/graph-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/graph-analytics.svg "Go to DockerHub Page"
[ml-dhrepo]: https://hub.docker.com/r/cloudsuite/twitter-dataset-graph/
[spark-dhrepo]: https://hub.docker.com/r/cloudsuite/spark/

[latestcontainer]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/graph-analytics/latest/Dockerfile "link to container, github"
