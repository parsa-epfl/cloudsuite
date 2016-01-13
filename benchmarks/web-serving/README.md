# WebServing

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

Web Serving is a main service in the cloud. Traditional web services with dynamic and static content are moved into the cloud to provide fault-tolerance and dynamic scalability by bringing up the needed number of servers behind a load balancer. Although many variants of the traditional web stack are used in the cloud (e.g., substituting Apache with other web server software or using other language interpreters in place of PHP), the underlying service architecture remains unchanged. Independent client requests are accepted by a stateless web server process which either directly serves static files from disk or passes the request to a stateless middleware script, written in a high-level interpreted or byte-code compiled language, which is then responsible for producing dynamic content. All the state information is stored by the middleware in backend databases such as cloud NoSQL data stores or traditional relational SQL servers supported by key-value cache servers to achieve high throughput and low latency. This benchmark includes a social networking engine (Elgg) and a client implemented using the Faban workload generator.

## Using the benchmark ##
The benchmark has four tiers: the web server, the database server, the memcached server, and the clients. The web server runs Elgg and it connects to the memcached server and the database server. The clients send requests to login to the social network. Each tier has its own image which is identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`web_server`][webserverdocker]: This represents the web server.
 - [`memcached_server`][memcacheserverdocker]: This represents the memcached server.
 - [`db_server`][mysqlserverdocker]: This represents the database server, which runs MySQL.
 - [`fabanclient`][clientdocker]: This represents the faban client.

These images are automatically built using the mentioned Dockerfiles available on the `CloudSuite-EPFL/WebServing` [GitHub repo][repo].

### Creating a network between the servers and the client(s)

To facilitate the communication between the client(s) and the servers, we build a docker network:

    $ docker network create my_net

We will attach the launched containers to this newly created docker network.

### Starting the web server ####
To start the web server, you first have to `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/webserving:web_server

The following command will start the web server, and attach it to the *my_net* network:

    $ docker run -d -t --net=my_net --privileged=true --name=mysql_server cloudsuite/webserving:web_server /etc/bootstrap.sh
    
### Starting the database server ####
To start the database server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/webserving:db_server

The following command will start the database server, and attach it to the *my_net* network:

    $ docker run -d -t --net=my_net --privileged=true --name=mysql_server cloudsuite/webserving:db_server
    
### Starting the memcached server ####
To start the memcached server, you have to first `pull` the server image. To `pull` the server image use the following command:

    $ docker pull cloudsuite/webserving:memcached_server

The following command will start the memcached server, and attach it to the *my_net* network:

    $ docker run run -d -t --net=my_net --privileged=true --name=memcache_server cloudsuite/webserving:memcached_server

### Starting the client ####

To start the client, you have to first `pull` the client image. To `pull` the client image use the following command:

    $ docker pull cloudsuite/webserving:faban_client

To start the client container and connect it to the *my_net* network use the following command:

    $ docker run -d -t --net=my_net --privileged=true --name=faban_client cloudsuite/webserving:faban_client /etc/bootstrap.sh -bash

###  Running the benchmark ###

To start the client, you need to run the run.sh file. 

    $ ./run.sh

You can find the summary of the results in the summary.xml file. 


  [webserverdocker]: https://github.com/CloudSuite-EPFL/WebServing/blob/master/web_server/Dockerfile "WebServer Dockerfile"
  [memcacheserverdocker]: https://github.com/CloudSuite-EPFL/WebServing/blob/master/memcached_server/Dockerfile "MemcacheServer Dockerfile"
  [mysqlserverdocker]: https://github.com/CloudSuite-EPFL/WebServing/blob/master/db_server/Dockerfile "MysqlServer Dockerfile"
  [clientdocker]: https://github.com/CloudSuite-EPFL/WebServing/blob/master/faban_client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/CloudSuite-EPFL/WebServing "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/webserving/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/webserving.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/webserving.svg "Go to DockerHub Page"
  
