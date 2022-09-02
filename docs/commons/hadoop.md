## Hadoop
Currently supported version is 2.10.1.

To obtain / build the image :
```
$ docker pull cloudsuite/hadoop:2.10.1

or

$ cd /path/to/cloudsuite/commons/hadoop/2.10.1
$ docker build --network host -t cloudsuite/hadoop:2.10.1 .
```

### Running Hadoop

**Note**: The following commands will run the Hadoop cluster within host's network. To make sure that slaves and master can communicate with each other, the master container's hostname, which should be host's hostname, must be able to be resolved to the same IP address by the master container and all slave containers. 

Start Hadoop master with:
```
$ docker run -d --net host --name master cloudsuite/hadoop:2.10.1 master
```

Start any number of Hadoop slaves with:
```
$ # on VM1
$ docker run -d --net host --name slave01 cloudsuite/hadoop:2.10.1 slave $MASTER_ADDRESS

$ # on VM2
$ docker run -d --net host --name slave02 cloudsuite/hadoop:2.10.1 slave $MASTER_ADDRESS

...
```
**Note**: You should set `MASTER_ADDRESS` to master's IP address.

Run the supplied example job (grep) with:
```
$ docker exec master /root/example_benchmark.sh
```
