# Data Analytics #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

Exploiting accessible human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. Hadoop is a popular approach to handling large-scale analysis with its distributed file system and compute capabilities, allowing it to scale to PetaBytes of data. The Data Analytics benchmark is included in CloudSuite to cover the increasing importance of classification tasks analyzing large amounts of data in datacenters using the MapReduce framework. It comprises Mahout, a set of machine learning libraries running on top of Hadoop, an open-source implementation of MapReduce.

The benchmark consists of running a Naive Bayes classifier on a (Wikimedia dataset)[https://dumps.wikimedia.org/backup-index.html]. It uses Hadoop version 2.10.2 and Mahout version 14.1.

## Running the benchmark ##

The benchmark is designed to run on a Hadoop cluster, where the single master runs the driver program, and the slaves run the mappers and reducers.

First, start the container for the dataset:

```bash
$ docker create --name wikimedia-dataset cloudsuite/wikimedia-pages-dataset 
```

Start the master with:

```bash
$ docker run -d --net host --volumes-from wikimedia-dataset --name data-master cloudsuite/data-analytics --master
```

By default, the Hadoop master node is listening on the first interface accessing to network. You can overwrite the listening address by adding `--master-ip=X.X.X.X` to change the setting.

Start any number of Hadoop workers with:

```
$ # on VM1
$ docker run -d --net host --name data-slave01 cloudsuite/data-analytics --slave --master-ip=<IP_ADDRESS_MASTER>

$ # on VM2
$ docker run -d --net host --name data-slave02 cloudsuite/data-analytics --slave --master-ip=<IP_ADDRESS_MASTER>

...
```

**Note**: You should set `IP_ADDRESS_MASTER` to the master's IP address and make sure that address is accessible from each worker.

After both master and worker are set up (you can use `docker logs` to observe if the log is still generating), run the benchmark with the following command:

```bash
$ docker exec data-master benchmark
```

### Configuring Hadoop parameters ###

We can configure a few parameters for Hadoop depending on requirements. 

Hadoop infers the number of workers using how many partitions it created with HDFS. We can increase or reduce the HDFS partition size to `N` MB with `--hdfs-block-size=N`, with 128MB being the default. The default dataset weighs 900MB. Thus, depending on the benchmark phase, the default option `-hdfs-block-size=128` results in splits between 1 and 8 parts.

The maximum number of workers is configured by `--yarn-cores=C`, whose default is 8. If there are more blocks than the number of workers, YARN will only allow up to `C` worker threads to process them. Please note that **at least two cores** should be given in total: One core for the map operation and another for the reduce operation. Otherwise, the process can get stuck. 

The maximum memory used by each worker is configured by `--mapreduce-mem=N`, and the default is 2096MB. Note that depending on the number of `--yarn-cores=C`, the total physical memory required will be at least `C*N`. To avoid out-of-memory errors, you are recommended to allocate 8GB of memory (even for a single worker with two cores) in total.

To increase the number of workers, please use a bigger dataset from wikimedia. Using smaller partition sizes than 128MB can increase the number of workers but slow down the execution due to overheads of the small partition size. 


[dhrepo]: https://hub.docker.com/r/cloudsuite/data-analytics/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-analytics.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-analytics.svg "Go to DockerHub Page"
