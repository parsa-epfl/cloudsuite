# Facebook Workload

The facebook benchmark readme file can be found in [README.md](https://github.com/facebookarchive/oss-performance/blob/v2019.02.13.00/README.md)

### Starting the database server ####
To start the database server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:db_server

The following command will start the database server:

    $ docker run -dt --net=host --name=mysql_server cloudsuite/web-serving:db_server


### Starting the facebook workload benchmark ####
To start the facebook workload benchmark you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/fb-workload:server
    
Create a file `cmd.sh` on the host which contains the information to connect to mysql as the first line `MysqlIP Username Password` and command to run the facebook workload on the second line. An example is shown below
```
192.168.1.64 root root
hhvm perf.php --i-am-not-benchmarking --mediawiki --db-host=192.168.1.62 --db-username=root --db-password=root --hhvm=$HHVM
    
$ chmod a+x cmd.sh
```
    
More info on parameters which can be provided to the facebook worload can be found in [README.md](https://github.com/facebookarchive/oss-performance/blob/v2019.02.13.00/README.md)

The following command will start the facebook server:

    $ docker run --cap-add sys_admin --net=host -v /<path>/cmd.sh:/oss-performance/cmd.sh cloudsuite/fb-workload:server
