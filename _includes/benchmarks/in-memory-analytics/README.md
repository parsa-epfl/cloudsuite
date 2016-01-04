### InMemoryAnalytics

The explosion of accessible human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. Recommender systems are a subclass of information filtering system that seek to predict the 'rating' or 'preference' that a user would give to an item. Recommender systems have become extremely common in recent years, and are applied in a variety of applications. The most popular ones are probably movies, music, news, books, research articles, search queries, social tags, and products in general. Because these applications suffer from I/O operations, nowadays, most of them are running in memory. The In Memory Analytics benchmark runs the alternating least squares (ALS) algorithm which is provided by Spark MLlib. 

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

### Running the benchmark

First create containers that host data and benchmark volumes that other
containers will be using:

    $ docker create --name data data
    $ docker create --name benchmarks benchmarks

Spark master is the cluster manager for Spark workers. Start Spark master with:

    $ docker run -dP --volumes-from data --volumes-from benchmarks \
      --hostname spark-master --name spark-master spark-master

Start one or more Spark workers with (use a different name and hostname for each worker):

    $ docker run -dP --volumes-from data --volumes-from benchmarks \
      --hostname spark-worker --name spark-worker spark-worker spark://spark-master:7077

Finally, run benchmark with the client:

    $ docker run --rm --volumes-from data --volumes-from benchmarks spark-client bench movielens-als

You can use Spark's web UI to monitor your jobs. Point your browser to MASTER_IP:8080, where: 

    $ MASTER_IP=$(docker inspect --format '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' spark-master)

[dhrepo]: https://hub.docker.com/r/cloudsuite/inmemoryanalytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/inmemoryanalytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/inmemoryanalytics.svg "Go to DockerHub Page"

