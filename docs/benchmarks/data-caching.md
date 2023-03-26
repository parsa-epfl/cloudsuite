# Data Caching #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses the Memcached data caching server. It simulates the behavior of a Twitter data caching server using a Twitter dataset. The metric of interest is throughput, expressed as the number of requests served per second. The workload assumes a strict Quality of Service (QoS) guarantee: the 99 percentile latency should be less than 1ms.

## Using the benchmark ##
This benchmark features two tiers: the server(s) running Memcached and the client(s) requesting data cached on the Memcached servers. Each tier has its own image, identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`server`][serverdocker] represents the Memcached server running as a daemon.
 - [`client`][clientdocker] represents the client which requests to access the server's data.

### Starting the Server ####

The following command will start a single Memcached server with four threads and 10GB of dedicated memory, with a minimum object size of 550 bytes listening on port 11211 as default:

```bash
$ docker run --name dc-server --net host -d cloudsuite/data-caching:server -t 4 -m 10240 -n 550
```

You may also set up multiple Memcached server instances using the following commands:

```bash
# on VM1
$ docker run --name dc-server1 --net host -d cloudsuite/data-caching:server -t 4 -m 10240 -n 550

# on VM2
$ docker run --name dc-server2 --net host -d cloudsuite/data-caching:server -t 4 -m 10240 -n 550

# ...
```    

### Starting the Client ####

Create an empty folder and then create a server configuration file named docker_servers.txt inside the folder. This file includes the server address and the port number to connect to, in the following format:
```
    server_address, port
```
The client can simultaneously use multiple servers or one server with several IP addresses (in case the server machine has multiple ethernet cards active) or one server through multiple ports, while measuring the overall throughput and QoS. As a result, each line in the configuration file should contain the corresponding server address and port number. To illustrate, in the case of our example, it would be:
```
    IP_ADDRESS_VM1, 11211
    IP_ADDRESS_VM2, 11211
    ...
```


To start the client container, use the following command:

```bash
$ docker run -idt --name dc-client --net host -v PATH_TO_DOCKER_SERVERS_FOLDER:/usr/src/memcached/memcached_client/docker_servers/ cloudsuite/data-caching:client
```

Please note that the command mounts the folder containing the docker_servers.txt file instead of only the file. This way, further changes to docker_servers.txt in the host will be reflected inside the container. 

#### Scaling the dataset and warming up the server ####

The following command will create the dataset by scaling up the Twitter dataset while preserving both popularity and object size distributions. The original dataset consumes ~360MB of server memory, while the recommended scaled dataset requires around 10GB of main memory dedicated to the Memcached server. Therefore, we use a scaling factor of 28 to have a 10GB dataset.

```bash
$ docker exec -it dc-client /bin/bash /entrypoint.sh --m="S&W" --S=28 --D=10240 --w=8 --T=1
```

(`m` - the mode of operation, `S&W` means scale the dataset and warm up the server, `w` - number of client threads which has to be divisible by the number of servers, `S` - scaling factor, `D` - target server memory, `T` - statistics interval).

If the scaled file already exists, but the server is not warmed up, use the following command to warm up the server. `W` refers to the _warm-up_ mode of operation.

```bash
$ docker exec -it dc-client /bin/bash /entrypoint.sh --m="W" --S=28 --D=10240 --w=8 --T=1
```
### Running the benchmark ###

To determine the maximum throughput while running the workload with 8 client threads,
200 TCP/IP connections, and a get/set ratio of 0.8, use the following command. `TH` refers to the _throughput_ mode of operation.

```bash
$ docker exec -it dc-client /bin/bash /entrypoint.sh --m="TH" --S=28 --g=0.8 --c=200 --w=8 --T=1
```

This command will run the benchmark with maximum throughput; however, the QoS requirements will likely be violated. Once the maximum throughput is determined, run the benchmark using the following command. `RPS` means the target load supplied by the client container.

```bash
$ docker exec -it dc-client /bin/bash /entrypoint.sh --m="RPS" --S=28 --g=0.8 --c=200 --w=8 --T=1 --r=rps
```

Where `rps` can start from the 90% of the maximum number of requests per second achieved using the previous command. It would be best to experiment with different `rps` values to achieve the maximum throughput without violating the target QoS requirements. By default, the request interval is fixed. You can add the `--ne` flag to make the interval follow a negative exponential distribution.

Note that the last two commands will continue forever if you do not stop or kill the command. You can use the timeout command to run a command for a given amount of time. The following example will run the benchmark in `RPS` mode for 20 seconds:

```bash
$ docker exec -it dc-client timeout 20 /bin/bash /entrypoint.sh --m="RPS" --S=28 --g=0.8 --c=200 --w=8 --T=1 --r=100000 
```

## Important remarks ##
- The target QoS requires that 99% of the requests are serviced within 1ms.

- Memcached has known scalability problems, scaling very poorly beyond four threads.
To utilize a machine with more than four cores,
you should start several server processes and add the corresponding parameters
into the client configuration file.
- The benchmark is network-intensive and thus requires a 10Gbit Ethernet card to not be network-bound. Multiple ethernet cards could be used as well, each with a different IP address, resulting in multiple servers in the client configuration file with the same socket but different IP addresses.


[memcachedWeb]: http://memcached.org/ "Memcached Website"

[serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/data-caching/server/Dockerfile "Server Dockerfile"

[clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/data-caching/client/Dockerfile "Client Dockerfile"

[repo]: https://github.com/parsa-epfl/cloudsuite "GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/data-caching/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/data-caching.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/data-caching.svg "Go to DockerHub Page"
