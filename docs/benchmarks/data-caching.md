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

### Starting the Server ####
To start the server you have to first `pull` the server image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/data-caching:server

It takes some time to download the image, but this is only required the first time.
The following command will start the server with four threads and 4096MB of dedicated memory, with a minimal object size of 550 bytes listening on port 11211 as default:

    $ docker run --name dc-server --net host -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550

 The following commands create Memcached server instances:

    $ # on VM1
    $ docker run --name dc-server1 --net host -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550

    $ # on VM2
    $ docker run --name dc-server2 --net host -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550
    ...
    

### Starting the Client ####

To start the client you have to first `pull` the client image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/data-caching:client
    
It takes some time to download the image, but this is only required the first time.
    
Create an empty folder and then create the server configuration file, named `docker_servers.txt` inside the folder. This file includes the server address and the port number to connect to, in the following format:

    server_address, port

The client can simultaneously use multiple servers, one server with several ip addresses (in case the server machine has multiple ethernet cards active), and one server through multiple ports, measuring the overall throughput and quality of service. In that case, each line in the configuration file should contain the server address and the port number. To illustrate, in the case of our example it should be:

    IP_ADDRESS_VM1, 11211
    IP_ADDRESS_VM2, 11211
    ...



To start the client container use the following command:

    $ docker run -idt --name dc-client --net host -v PATH_TO_DOCKER_SERVERS_FOLDER:/usr/src/memcached/memcached_client/docker_servers/ cloudsuite/data-caching:client

Please note that the command mounts the folder containing the 'docker_servers.txt' file instead of mounting only the file. This way, further changes to the docker_servers.txt file in the host will be reflected inside of the container. 

#### Scaling the dataset and warming up the server ####

The following command will create the dataset by scaling up the Twitter dataset, while preserving both the popularity and object size distributions. The original dataset consumes 300MB of server memory, while the recommended scaled dataset requires around 10GB of main memory dedicated to the Memcached server (scaling factor of 30).

    $ docker exec -it dc-client /bin/bash /entrypoint.sh --m="S&W" --S=30 --D=1024 --w=8 --T=1
    
(`m` - the mode of operation, `S&M` means scale the dataset and warm up the server, `w` - number of client threads which has to be divisible to the number of servers, `S` - scaling factor, `D` - target server memory, `T` - statistics interval).

If the scaled file is already created, but the server is not warmed up, use the following command to warm up the server. `W` refers to the _warm up_ mode of operation.

    $ docker exec -it dc-client /bin/bash /entrypoint.sh --m="W" --S=30 --D=1024 --w=8 --T=1

### Running the benchmark ###

To determine the maximum throughput while running the workload with eight client threads,
200 TCP/IP connections, and a get/set ratio of 0.8, use the following command. `TH` refers to the _throughput_ mode of operation.

    $ docker exec -it dc-client /bin/bash /entrypoint.sh --m="TH" --S=30 --g=0.8 --c=200 --w=8 --T=1 

This command will run the benchmark with the maximum throughput; however, the QoS requirements will highly likely be violated. Once the maximum throughput is determined, you should run the benchmark using the following command. `RPS` means that the client container will keep the load at the given load (requests per second).   

    $ docker exec -it dc-client /bin/bash /entrypoint.sh --m="RPS" --S=30 --g=0.8 --c=200 --w=8 --T=1 --r=rps 

where `rps` is 90% of the maximum number of requests per second achieved using the previous command. You should experiment with different values of `rps` to achieve the maximum throughput without violating the target QoS requirements.

Note that the last two commands will continue forever if you do not stop or kill the command. For running the command for a given amount of time, you can use the timeout command. The following example will run the benchmark in the `RPS` mode for 20 seconds:

    $ docker exec -it dc-client timeout 20 /bin/bash /entrypoint.sh --m="RPS" --S=30 --g=0.8 --c=200 --w=8 --T=1 --r=100000 

## Important remarks ##
- It takes several minutes for the server to reach a stable state.

- The target QoS requires that 95% of the requests are serviced within 10 ms.

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
