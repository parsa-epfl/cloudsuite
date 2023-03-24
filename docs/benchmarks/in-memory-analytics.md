# In-Memory Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses Apache Spark and runs a collaborative filtering algorithm in memory on a dataset of user-movie ratings. The metric of interest is the time in seconds for computing movie recommendations.

The explosion of human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. Recommender systems are a subclass of information filtering systems that seek to predict the 'rating' or 'preference' that a user would give to an item. They have become extremely popular in recent years and are applied in various applications. Movies, music, news, books, research articles, search queries, and social tags are the most popular. Because these applications suffer from I/O operations, most are running in memory nowadays. This benchmark runs the alternating least squares (ALS) algorithm provided by Spark MLlib.

### Datasets

The benchmark uses user-movie ratings datasets provided by Movielens. To get
the dataset image:

```sh
    $ docker pull cloudsuite/movielens-dataset
```

More information about the dataset is available at
[cloudsuite/movielens-dataset][ml-dhrepo].

### Running the Benchmark

The benchmark runs the ALS algorithm on Spark through the spark-submit script distributed with Spark. It takes two arguments: the dataset for training and the personal ratings file for recommendations. We provide two training datasets (one small and one large) and a sample personal ratings file.

To run the benchmark with the small dataset and the provided personal ratings file:

```sh
    $ docker create --name movielens-data cloudsuite/movielens-dataset
    $ docker run --volumes-from movielens-data cloudsuite/in-memory-analytics \
        /data/ml-latest-small /data/myratings.csv
```

Any remaining arguments are passed to `spark-submit`.

### Tweaking the Benchmark

Any arguments after the two mandatory ones are passed to spark-submit and are used to tweak execution. For example, to ensure that Spark has enough memory allocated to be able to execute the benchmark in memory, supply it with --driver-memory and --executor-memory arguments:

```sh
    $ docker run --volumes-from movielens-data cloudsuite/in-memory-analytics \
        /data/ml-latest /data/myratings.csv \
        --driver-memory 4g --executor-memory 4g
```

### Multi-node deployment

This section explains how to run the benchmark using multiple Spark workers (each running in a Docker container) that can be spread across multiple nodes in a cluster. 

First, create a dataset image on every physical node where Spark workers will be running.

```sh
    $ docker create --name movielens-data cloudsuite/movielens-dataset
```

Start Spark master and Spark workers. You can start the master node with the following command:

```sh
    $ docker run -dP --net host --name spark-master \
        cloudsuite/spark:3.3.2 master
```

By default, the container uses the hostname as the listening IP for the connections to the worker nodes. Ensure all worker machines can access the master machine using the master hostname if the listening IP is kept by default. You can also override the listening address by overriding the environment variable `SPARK_MASTER_IP` with the container option `-e SPARK_MASTER_IP=X.X.X.X`.

The workers get access to the datasets with `--volumes-from movielens-data`.

```
    $ docker run -dP --net host --volumes-from movielens-data --name spark-worker-01 \
        cloudsuite/spark:3.3.2 worker spark://SPARK-MASTER:7077
    $ docker run -dP --net host --volumes-from movielens-data --name spark-worker-02 \
        cloudsuite/spark:3.3.2 worker spark://SPARK-MASTER:7077
    $ ...
```

`SPARK_MASTER` is Spark master's listening address.

Finally, run the benchmark as the client to the Spark master:

```
    $ docker run --rm --net host --volumes-from movielens-data \
                 cloudsuite/in-memory-analytics \
                 /data/ml-latest-small /data/myratings.csv \
                 --driver-memory 4g --executor-memory 4g \
                 --master spark://SPARK-MASTER:7077
```

[dhrepo]: https://hub.docker.com/r/cloudsuite/in-memory-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/in-memory-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/in-memory-analytics.svg "Go to DockerHub Page"
[ml-dhrepo]: https://hub.docker.com/r/cloudsuite/movielens-dataset/ 
[spark-dhrepo]: https://hub.docker.com/r/cloudsuite/spark/
