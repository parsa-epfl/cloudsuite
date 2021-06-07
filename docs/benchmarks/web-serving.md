# Web Serving

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

Web Serving is a main service in the cloud. Traditional web services with dynamic and static content are moved into the cloud to provide fault-tolerance and dynamic scalability by bringing up the needed number of servers behind a load balancer. Although many variants of the traditional web stack are used in the cloud (e.g., substituting Apache with other web server software or using other language interpreters in place of PHP), the underlying service architecture remains unchanged. Independent client requests are accepted by a stateless web server process which either directly serves static files from disk or passes the request to a stateless middleware script, written in a high-level interpreted or byte-code compiled language, which is then responsible for producing dynamic content. All the state information is stored by the middleware in backend databases such as cloud NoSQL data stores or traditional relational SQL servers supported by key-value cache servers to achieve high throughput and low latency. This benchmark includes a social networking engine (Elgg) and a client implemented using the Faban workload generator.

Furthermore, we have recently incorporated Facebook''s HipHop Virtual Machine compiler and runtime for producing JIT-compiled PHP scripts. For more information, you can read about HHVM [here](https://hhvm.com/).

## Using the benchmark ##
The benchmark has four tiers: the web server, the database server, the memcached server, and the clients. The web server runs Elgg and it connects to the memcached server and the database server. The clients send requests to login to the social network. Each tier has its own image which is identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`web_server`][webserverdocker]: This represents the web server.
 - [`memcached_server`][memcacheserverdocker]: This represents the memcached server.
 - [`db_server`][mysqlserverdocker]: This represents the database server, which runs MySQL.
 - [`faban_client`][clientdocker]: This represents the faban client.

These images are automatically built using the mentioned Dockerfiles available on the `parsa-epfl/cloudsuite` [GitHub repo][repo].

### Starting the database server ####
To start the database server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:db_server

The following command will start the database server:

    $ docker run -dt --net=host --name=mysql_server cloudsuite/web-serving:db_server ${WEB_SERVER_IP}

The ${WEB_SERVER_IP}  parameter is mandatory. It sets the IP of the web server. If you are using the host network, the web server IP is the IP of the machine that you are running the web_server container on. If you create your own network you can use the name that you are going to give to the web server (we called it web_server in the following commands).

### Starting the memcached server ####
To start the memcached server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:memcached_server

The following command will start the memcached server:

    $ docker run -dt --net=host --name=memcache_server cloudsuite/web-serving:memcached_server

### Starting the web server ####
To start the web server, you first have to `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:web_server

To run the web server *without HHVM*, use the following command:

    $ docker run -dt --net=host --name=web_server cloudsuite/web-serving:web_server /etc/bootstrap.sh ${DATABASE_SERVER_IP} ${MEMCACHED_SERVER_IP} ${MAX_PM_CHILDREN}

To run the web server *with HHVM enabled*, use the following command:

    $ docker run -e "HHVM=true" -dt --net=host --name=web_server_local cloudsuite/web-serving:web_server /etc/bootstrap.sh ${DATABASE_SERVER_IP} ${MEMCACHED_SERVER_IP} ${MAX_PM_CHILDREN}

The three ${DATABASE_SERVER_IP},${MEMCACHED_SERVER_IP}, and ${MAX_PM_CHILDREN} parameters are optional. The ${DATABASE_SERVER_IP}, and ${MEMCACHED_SERVER_IP} show the IP (or the container name) of the database server, and the IP (or the container name) of the memcached server, respectively. For example, if you are running all the containers on the same machine and use the host network you can use the localhost IP (127.0.0.1). Their default values are mysql_server, and memcache_server, respectively, which are the default names of the containers. 
The ${MAX_PM_CHILDREN} set the pm.max_children in the php-fpm setting. The default value is 80. 

###  Running the benchmark ###

First `pull` the client image use the following command:

    $ docker pull cloudsuite/web-serving:faban_client

To start the client container which runs the benchmark, use the following commands:

    $ docker run --net=host --name=faban_client cloudsuite/web-serving:faban_client ${WEB_SERVER_IP} ${LOAD_SCALE}

The last command has a mandatory parameter to set the IP of the web_server, and an optional parameter to set the load scale (default is 7). The `LOAD_SCALE` parameter controls the number of users that are simultaneously logging in to the web servers and requesting social networking pages. You can scale it up and down as much as you would like, keeping in mind that scaling the number of threads may stress the system. To tune the benchmark, we recommend testing your machines for the maximum possible request throughput, while maintaining your target QoS metric (we use 99th percentile latency). CPU utilization is less important than the latency and responsiveness for these benchmarks.

The last command will output the summary of the benchmark results in XML at the end of the output. You can also access the summary and logs of the run by mounting the `/faban/output` directory of the container in the host filesystem (e.g. `-v /host/path:/faban/output`).

  [webserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/web-serving/web_server/Dockerfile "WebServer Dockerfile"
  [memcacheserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/web-serving/memcached_server/Dockerfile "MemcacheServer Dockerfile"
  [mysqlserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/web-serving/db_server/Dockerfile "MysqlServer Dockerfile"
  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/web-serving/faban_client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite/tree/master/benchmarks/web-serving "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/web-serving/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/web-serving.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/web-serving.svg "Go to DockerHub Page"
