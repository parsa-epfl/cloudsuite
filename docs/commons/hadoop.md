## Hadoop
Currently supported version is 2.10.1

To obtain / build the image :
```
$ docker pull cloudsuite/hadoop:2.10.1

or

$ cd /path/to/cloudsuite/commons/hadoop/2.10.1
$ docker build --network host -t cloudsuite/hadoop:2.10.1
```

### Running Hadoop
Start Hadoop master with:
```
$ docker run -d --net host --name master cloudsuite/hadoop:2.10.1 master
```

Start any number of Hadoop slaves with:
```
$ # on VM1
$ docker run -d --net host --name slave01 cloudsuite/hadoop:2.10.1 slave $IP_ADRESS_MASTER

$ # on VM2
$ docker run -d --net host --name slave02 cloudsuite/hadoop:2.10.1 slave $IP_ADRESS_MASTER

...
```
Note : Start each slave on a different VM.

Run the supplied example job (grep) with:
```
$ docker exec master /root/example_benchmark.sh
```
