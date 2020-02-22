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

    $ docker pull cloudsuite/in-memory-analytics

### Datasets

The benchmark uses user-movie ratings datasets provided by Movielens. To get
the dataset image:

    $ docker pull cloudsuite/movielens-dataset

More information about the dataset is available at
[cloudsuite/movielens-dataset][ml-dhrepo].

### Running the Benchmark

The benchmark runs the ALS algorithm on Spark through the spark-submit script
distributed with Spark. It takes two arguments: the dataset to use for
training, and the personal ratings file to give recommendations for. Any
remaining arguments are passed to spark-submit.

The cloudsuite/movielens-dataset image has two datasets (one small and one
large), and a sample personal ratings file.

To run a benchmark with the small dataset and the provided personal ratings
file:

    $ docker create --name movielens-data cloudsuite/movielens-dataset
    $ docker run --rm --volumes-from movielens-data cloudsuite/in-memory-analytics \
        /data/ml-latest-small /data/myratings.csv

### Tweaking the Benchmark

Any arguments after the two mandatory ones are passed to spark-submit and can
be used to tweak execution. For example, to ensure that Spark has enough memory
allocated to be able to execute the benchmark in-memory, supply it with
--driver-memory and --executor-memory arguments:

    $ docker run --rm --volumes-from data cloudsuite/in-memory-analytics \
        /data/ml-latest /data/myratings.csv \
        --driver-memory 2g --executor-memory 2g

### Multi-node deployment

This section explains how to run the benchmark using multiple Spark workers
(each running in a Docker container) that can be spread across multiple nodes
in a cluster. For more information on running Spark with Docker look at
[cloudsuite/spark][spark-dhrepo].

First, create a dataset image on every physical node where Spark workers will
be running.

    $ docker create --name movielens-data cloudsuite/movielens-dataset

Start Spark master and Spark workers. They should all run within the same
Docker network, which we call spark-net here. The workers get access to the
datasets with --volumes-from movielens-data.

    $ docker run -dP --net host --name spark-master \
        cloudsuite/spark:2.3.1 master
    $ docker run -dP --net host --volumes-from movielens-data --name spark-worker-01 \
        cloudsuite/spark:2.3.1 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ docker run -dP --net host --volumes-from movielens-data --name spark-worker-02 \
        cloudsuite/spark:2.3.1 worker spark://SPARK-MASTER-IPADDRESS:7077
    $ ...

Finally, run the benchmark as the client to the Spark master:

    $ docker run --rm --net host --volumes-from movielens-data \
                 cloudsuite/in-memory-analytics \
                 /data/ml-latest-small /data/myratings.csv \
                 --driver-memory 4g --executor-memory 4g \
                 --master spark://SPARK-MASTER-IPADDRESS:7077

[dhrepo]: https://hub.docker.com/r/cloudsuite/in-memory-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/in-memory-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/in-memory-analytics.svg "Go to DockerHub Page"
[ml-dhrepo]: https://hub.docker.com/r/cloudsuite/movielens-dataset/ 
[spark-dhrepo]: https://hub.docker.com/r/cloudsuite/spark/
