# Web Serving

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

Web Serving is a main service in the cloud. Traditional web services with dynamic and static content are moved into the cloud to provide fault-tolerance and dynamic scalability by bringing up the needed number of servers behind a load balancer. Although many variants of the traditional web stack are used in the cloud (e.g., substituting Apache with other web server software or using other language interpreters in place of PHP), the underlying service architecture remains unchanged. Independent client requests are accepted by a stateless web server process that either directly serves static files from disk or passes the request to a stateless middleware script, written in a high-level interpreted or byte-code compiled language, which is then responsible for producing dynamic content. All the state information is stored by the middleware in backend databases such as cloud NoSQL data stores or traditional relational SQL servers supported by key-value cache servers to achieve high throughput and low latency. This benchmark includes a social networking engine (Elgg) and a client implemented using the Faban workload generator. This version of the benchmark is compatible with Php 8.1 which supports JIT execution to further improve the workload's throughput.

## Using the benchmark ##
The benchmark has four tiers: the web server, the database server, the Memcached server, and the clients. The web server runs Elgg and it connects to the Memcached server and the database server. The clients send requests to log in to the social network and perform various operations including sending and reading the messages, adding or removing friends, posting on the wall, posting blogs, and adding comments or likes to different posts. Each tier has its image which is identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`web_server`][webserverdocker]: This represents the web server.
 - [`memcached_server`][memcacheserverdocker]: This represents the Memcached server.
 - [`db_server`][databaseserverdocker]: This represents the database server, which runs MariaDB.
 - [`faban_client`][clientdocker]: This represents the faban client.

These images are automatically built using the mentioned Dockerfiles available on the `parsa-epfl/cloudsuite` [GitHub repo][repo].

### IP Addresses ###
Please note that all IP addresses should refer to the explicit IP address of the host server running each container.

### Starting the database server ####
To start the database server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:db_server

The following command will start the database server:

    $ docker run -dt --net=host --name=database_server cloudsuite/web-serving:db_server

The benchmark starts with a pre-populated database which stores around 100 K users and their data such as their friends' list and sent messages. The size of the database is around 2.5 GB. Based on your need, you can extend the database size by running the benchmark for some time and inspecting whether the size of the database has reached your desired number.   

### Starting the memcached server ####
To start the Memcached server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:memcached_server

The following command will start the Memcached server:

    $ docker run -dt --net=host --name=memcache_server cloudsuite/web-serving:memcached_server

### Starting the web server ####
To start the web server, you first have to `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/web-serving:web_server

To run the web server, use the following command:

    $ docker run -dt --net=host --name=web_server cloudsuite/web-serving:web_server /etc/bootstrap.sh ${PROTOCOL} ${WEB_SERVER_IP} ${DATABASE_SERVER_IP} ${MEMCACHED_SERVER_IP} ${MAX_PM_CHILDREN}

The `PROTOCOL` parameter can either be `http` or `https` and determines the protocol that the web server will use. The `WEB_SERVER_IP`, `DATABASE_SERVER_IP`, and `MEMCACHED_SERVER_IP` parameters refer to the explicit IP of the server running each server. The `MAX_PM_CHILDREN` sets the pm.max_children in the php-fpm setting. The default value is 80. 

###  Running the benchmark ###

First `pull` the client image using the following command:

    $ docker pull cloudsuite/web-serving:faban_client

To start the client container which runs the benchmark, use the following commands:

    $ docker run --net=host --name=faban_client cloudsuite/web-serving:faban_client ${WEB_SERVER_IP} ${LOAD_SCALE}

The last command has a mandatory parameter to set the IP of the web_server, and an optional parameter to set the load scale (default is 1). The `LOAD_SCALE` parameter controls the number of users that are simultaneously logging in to the web server and requesting social networking pages. You can scale it up and down as much as you would like, keeping in mind that scaling the number of threads may stress the system. To tune the benchmark, we recommend testing your machines for the maximum possible request throughput, while maintaining your target QoS metric (we use 99th percentile latency). CPU utilization is less important than the latency and responsiveness for these benchmarks.

The client container offers a bunch of options to control the way the benchmark runs. The list of the available options is as follows:

- `--oper`: This option has three possible values: `usergen`, `run`, and `usergen&run`. In `usergen`, the benchmark only creates new users that are added to the database. The number of generated users is determined by `LOAD_SCALE`. On the other hand, `run` does not generate new users but starts the benchmark by logging in the users to interact with the server by sending various requests. Note that ~100 K users are already registered in the benchmark's database. Finally, `usergen&run` does what previous operations do together. The default value is `usergen&run`.
- `--ramp-up`: the number of seconds the benchmark spends in the ramp-up phase. Keep in mind that there is a one-second distance between the users' log-in requests. Therefore, please set the ramp-up time to a value larger than `LOAD_SCALE` to make sure in the steady state, the number of active users matches your given number. The default value is 10.
- `--ramp-down`: the number of seconds the benchmark spends in the ramp-down phase. The default value is 10. 
- `--steady`: the number of seconds the benchmark spends in the steady-state phase where all users are logged in and interacting with the server. The default value is 30.
- `--min`: the minimum number of milliseconds between the consecutive requests of a specific user. The default value is 1000.
- `--max`: the maximum number of milliseconds between the consecutive requests of a specific user. The default value is 1500.
- `--type`: This option has two possible values: `THINKTIME` and `CYCLETIME`. In the former, the latencies determined by `--min` and `--max` to send a new request are measured compared to the point when the last request finishes. On the other hand, in `CYCLETIME`, the latencies are calculated from the point the request is sent to the server. The default value is `THINKTIME`.
- `--dist`: This option controls the distribution of the values chosen for the inter-request latencies between `--min` and `--max`. You can give three possible values to this option: `fixed`, `uniform`, and `negexp` (that refers to the negative exponential distribution). The default value is `fixed`.
- `--encryption`: The client container can send the request to a TLS encrypted server by setting this option to 1. The default value is 0 which means the requests are sent over HTTP.

The last command will output the summary of the benchmark results in XML at the end of the output. You can also access the summary and logs of the run by mounting the `/faban/output` directory of the container in the host filesystem (e.g. `-v /host/path:/faban/output`).

  [webserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/web_server/Dockerfile "WebServer Dockerfile"
  [memcacheserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/memcached_server "MemcacheServer Dockerfile"
  [databaseserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/db_server/Dockerfile "DatabaseServer(MariaDB) Dockerfile"
  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/faban_client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/web-serving "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/web-serving/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/web-serving.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/web-serving.svg "Go to DockerHub Page"
