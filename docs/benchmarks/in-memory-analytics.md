# In-Memory Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses Apache Spark and runs a collaborative filtering algorithm
in-memory on a dataset of user-movie ratings. The metric of interest is the
time in seconds of computing movie recommendations.

The explosion of accessible human-generated information necessitates automated
analytical processing to cluster, classify, and filter this information.
Recommender systems are a subclass of information filtering system that seek to
predict the 'rating' or 'preference' that a user would give to an item.
Recommender systems have become extremely common in recent years, and are
applied in a variety of applications. The most popular ones are movies, music,
news, books, research articles, search queries, social tags, and products in
general. Because these applications suffer from I/O operations, nowadays, most
of them are running in memory. This benchmark runs the alternating least
squares (ALS) algorithm which is provided by Spark MLlib.

### Getting the Image

Current version of the benchmark is 3.0. To obtain the image:

    $ docker pull cloudsuite3/in-memory-analytics

### Datasets

The benchmark uses user-movie ratings datasets provided by Movielens. To get
the dataset image:

    $ docker pull cloudsuite3/movielens-dataset

More information about the dataset is available at
[cloudsuite3/movielens-dataset][ml-dhrepo].

### Running the Benchmark

The benchmark runs the ALS algorithm on Spark through the spark-submit script
distributed with Spark. It takes two arguments: the dataset to use for
training, and the personal ratings file to give recommendations for. Any
remaining arguments are passed to spark-submit.

The cloudsuite3/movielens-dataset image has two datasets (one small and one
large), and a sample personal ratings file.

To run a benchmark with the small dataset and the provided personal ratings
file:

    $ docker create --name data cloudsuite3/movielens-dataset
    $ docker run --rm --volumes-from data cloudsuite3/in-memory-analytics \
        /data/ml-latest-small /data/myratings.csv

### Tweaking the Benchmark

Any arguments after the two mandatory ones are passed to spark-submit and can
be used to tweak execution. For example, to ensure that Spark has enough memory
allocated to be able to execute the benchmark in-memory, supply it with
--driver-memory and --executor-memory arguments:

    $ docker run --rm --volumes-from data cloudsuite3/in-memory-analytics \
        /data/ml-latest /data/myratings.csv \
        --driver-memory 2g --executor-memory 2g

### Multi-node deployment

This section explains how to run the benchmark using multiple Spark workers
(each running in a Docker container) that can be spread across multiple nodes
in a cluster. For more information on running Spark with Docker look at
[cloudsuite3/spark][spark-dhrepo].

First, create a dataset image on every physical node where Spark workers will
be running.

    $ docker create --name data cloudsuite3/movielens-dataset

Then, create dedicated network for spark workers:

    $ docker network create spark-net

Start Spark master and Spark workers. They should all run within the same
Docker network, which we call spark-net here. The workers get access to the datasets with --volumes-from data.

    $ docker run -dP --net spark-net --hostname spark-master --name spark-master cloudsuite3/spark master
    $ docker run -dP --net spark-net --volumes-from data --name spark-worker-01 cloudsuite3/spark worker \
        spark://spark-master:7077
    $ docker run -dP --net spark-net --volumes-from data --name spark-worker-02 cloudsuite3/spark worker \
        spark://spark-master:7077
    $ ...

Finally, run the benchmark as the client to the Spark master:

    $ docker run --rm --net spark-net --volumes-from data cloudsuite3/in-memory-analytics \
        /data/ml-latest-small /data/myratings.csv --master spark://spark-master:7077

[dhrepo]: https://hub.docker.com/r/cloudsuite3/in-memory-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite3/in-memory-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite3/in-memory-analytics.svg "Go to DockerHub Page"
[ml-dhrepo]: https://hub.docker.com/r/cloudsuite3/movielens-dataset/ 
[spark-dhrepo]: https://hub.docker.com/r/cloudsuite3/spark/

