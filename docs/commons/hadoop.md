## Hadoop

Currently supported version is 2.9.2 for Cloudsuite 3.0.

To obtain the image:
```
$ docker pull cloudsuite3/hadoop
```

### Running Hadoop

First, create a network to isolate your Hadoop cluster:
```
$ docker network create hadoop-net
```

Start Hadoop master with:
```
$ docker run -d --net hadoop-net --name master --hostname master cloudsuite3/hadoop master
```

Start any number of Hadoop slaves with:
```
$ docker run -d --net hadoop-net --name slave01 --hostname slave01 cloudsuite3/hadoop slave
$ docker run -d --net hadoop-net --name slave02 --hostname slave02 cloudsuite3/hadoop slave
...
```

Note that it is important to set hostnames. Hostname should be the same as
name. If master's name/hostname isn't "master", then it should be supplied to
slaves when running the containers as an argument after "slave".

Run the supplied example job (grep) with:
```
$ docker exec master /root/example_benchmark.sh
```
