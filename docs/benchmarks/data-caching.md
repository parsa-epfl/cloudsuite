# Data Caching #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses the [Memcached][memcachedWeb] data caching server,
simulating the behavior of a Twitter caching server using a twitter dataset.
The metric of interest is throughput expressed as the number of requests served per second.
The workload assumes strict quality of service guarantees.

## Using the benchmark ##
This benchmark features two tiers: the server(s), running Memcached, and the client(s), which request data cached on the Memcached servers. Each tier has its own image which is identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`server`][serverdocker]: This represents the Memcached server running as a daemon.
 - [`client`][clientdocker]: This represents the client which requests to access the server's data.

These images are automatically built using the mentioned Dockerfiles available on `parsa-epfl/cloudsuite` [GitHub repo][repo].

### Preparing a network between the server(s) and the client

To facilitate the communication between the client and the server(s), we build a docker network:

    $ docker network create caching_network

We will attach the launched containers to this newly created docker network.

### Starting the Server ####
To start the server you have to first `pull` the server image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/data-caching:server

It takes some time to download the image, but this is only required the first time.
The following command will start the server with four threads and 4096MB of dedicated memory, with a minimal object size of 550 bytes listening on port 11211 as default:

    $ docker run --name dc-server --net caching_network -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550

We assigned a name to this server to facilitate linking it with the client. We also used `--net` option to attach the container to our prepared network.
As mentioned before, you can have multiple instances of the Memcached server, just remember to give each of them a unique name. For example, the following commands create four Memcached server instances:

    $ docker run --name dc-server1 --net caching_network -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550
    $ docker run --name dc-server2 --net caching_network -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550
    $ docker run --name dc-server3 --net caching_network -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550
    $ docker run --name dc-server4 --net caching_network -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550

### Starting the Client ####

To start the client you have to first `pull` the client image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/data-caching:client

It takes some time to download the image, but this is only required the first time.

To start the client container use the following command:

    $ docker run -it --name dc-client --net caching_network cloudsuite/data-caching:client bash

This boots up the client container and you'll be logged in as the `memcache` user. Note that by using the `--net` option, you can easily make these containers visible to each other.

Before running the actual benchmark, you need to prepare the client.

#### Preparing the Client #####

All the required files for benchmarking are already placed in a directory in this image.
Use the following command to change your active directory to this directory:

    $ cd /usr/src/memcached/memcached_client/

Prepare the server configuration file, `docker_servers.txt`, which includes the server address and the port number to connect to, in the following format:

    server_address, port

The client can simultaneously use multiple servers, one server with several ip addresses (in case the server machine has multiple ethernet cards active), and one server through multiple ports, measuring the overall throughput and quality of service. In that case, each line in the configuration file should contain the server address and the port number. To illustrate, in the case of our example it should be:

    dc-server1, 11211
    dc-server2, 11211
    dc-server3, 11211
    dc-server4, 11211

You can use the `vim` command for modifying this file inside the container.

#### Scaling the dataset and warming up the server ####

The following command will create the dataset by scaling up the Twitter dataset, while preserving both the popularity and object size distributions. The original dataset consumes 300MB of server memory, while the recommended scaled dataset requires around 10GB of main memory dedicated to the Memcached server (scaling factor of 30).

    $ ./loader -a ../twitter_dataset/twitter_dataset_unscaled -o ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -w 4 -S 30 -D 4096 -j -T 1

(`w` - number of client threads which has to be divisible to the number of servers, `S` - scaling factor, `D` - target server memory, `T` - statistics interval, `s` - server configuration file, `j` - an indicator that the server should be warmed up).

If the scaled file is already created, but the server is not warmed up, use the following command to warm up the server:

    $ ./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -w 4 -S 1 -D 4096 -j -T 1

### Running the benchmark ###

To determine the maximum throughput while running the workload with eight client threads,
200 TPC/IP connections, and a get/set ratio of 0.8, use the following command:

    $ ./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -g 0.8 -T 1 -c 200 -w 8

This command will run the benchmark with the maximum throughput; however, the QoS requirements will highly likely be violated. Once the maximum throughput is determined, you should run the benchmark using the following command:

    $ ./loader -a ../twitter_dataset/twitter_dataset_30x -s docker_servers.txt -g 0.8 -T 1 -c 200 -w 8 -e -r rps

where `rps` is 90% of the maximum number of requests per second achieved using the previous command. You should experiment with different values of `rps` to achieve the maximum throughput without violating the target QoS requirements.

When you are done with benchmarking, just type `exit` to quit the client container.
As the server containers are running as daemons, you have to stop them using `docker`:

    $ docker stop dc-server1 dc-server2 dc-server3 dc-server4

## Important remarks ##
- It takes several minutes for the server to reach a stable state.

- The target QoS requires that 95% of the requests are serviced within 10ms.

- Memcached has known scalability problems, scaling very poorly beyond four threads.
To utilize a machine with more than four cores,
you should start several server processes and add the corresponding parameters
into the client configuration file.
- The benchmark is network-intensive and thus requires a 10Gbit Ethernet card
not to be network-bound. Multiple ethernet cards could be used as well,
each with a different IP address (two servers in the client configuration file
with the same socket, but different IP address).
Multisocket machines could also mitigate the network bandwidth limitations by running the server
and the client on different sockets of the same machine
(e.g., pinned using taskset), communicating via localhost.



  [memcachedWeb]: http://memcached.org/ "Memcached Website"

  [serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/data-caching/server/Dockerfile "Server Dockerfile"

  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/data-caching/client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/data-caching/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-caching.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-caching.svg "Go to DockerHub Page"
