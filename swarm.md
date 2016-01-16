---
layout: page
title: Setting up a Docker Swarm cluster
---

This document explains by example how to set up a cluster of Docker nodes (a
Docker Swarm), and how to set up an overlay network between nodes in this
cluster, so that containers started on the nodes can communicate.

The Docker overlay network requires a key-value store reachable from every node to
store the network's configuration. We will be using Consul for the key-value store, and also
for node discovery.

#### Get images

We will need the Swarm image on all nodes in the cluster.

    docker pull swarm

We will need the Consul image on one of the nodes.

    docker pull progrium/consul

#### Configure Docker daemons

We need to configure Docker daemons running on all nodes to:
1. Listen on port 2375 for connection from the Swarm manager, in addition to
   listening on a local Unix socket.
2. Be able to find the Consul store.
3. Advertise the Consul host's IP for the Swarm manager to discover.

On ubuntu, add the following to /etc/default/docker:

    DOCKER_OPTS="-H unix:///var/run/docker.sock -H tcp://0.0.0.0:2375 \
                 --cluster-store consul:/$CONSUL_HOST_IP:8500 \
                 --cluster-advertise eth1:2375"

This assumes you have defined the environment variable CONSUL_HOST_IP:

    export CONSUL_HOST_IP=<Consul node IP>

Then restart the Docker service:

    sudo service docker restart

#### Create the key-value store

    docker run -d -p 8500:8500 --hostname consul --name consul-store progrium/consul -server -bootstrap

#### Create the Swarm

Start a Swarm agent on each node to advertise the node to Consul:

    docker run -d --name swarm-agent swarm join --advertise=`hostname`:2375 consul://$CONSUL_HOST_IP:8500

Start a Swarm manager on one node (we will use port 22222 as the manager port):

    docker run -d -p 22222:2375 --name swarm-manager swarm manage consul://$CONSUL_HOST_IP:8500

To verify that the Swarm is set up correctly, you can run:

    export MANAGER_HOST_IP=<Swarm manager node IP>
    docker -H tcp://$MANAGER_HOST_IP:22222 info

This should give you the listing of nodes, with the number of containers
running on them, among other information. You can also list the advertised
nodes with:

    docker run --rm swarm list consul://$CONSUL_HOST_IP:8500

#### Create the overlay network

With an overlay network, containers running on different nodes can see each other. This
is implemented by adding the container name to the hosts file of all other
containers on the network when they are started. To create an overlay network for use with our Swarm:

    docker -H tcp://$MANAGER_HOST_IP:22222 network create --driver overlay swarm-network

To check that the network is created:

    docker -H tcp://$MANAGER_HOST_IP:22222 network ls

#### Example 1: running containers with Swarm and swarm-network

In the example, we will run two containers from the Ubuntu image on different
nodes of the cluster (let's call them nodes n1 and n2), attaching them to the
swarm-network overlay network, and ping one from the other for 3s.

Run the containers, and verify that they are running on different nodes from
the output of `docker ps`:

    docker -H tcp://$MANAGER_HOST_IP:22222 run -itd --net swarm-network -e constraint:node==n1 --name u1 ubuntu
    docker -H tcp://$MANAGER_HOST_IP:22222 run -itd --net swarm-network -e constraint:node==n2 --name u2 ubuntu
    docker -H tcp://$MANAGER_HOST_IP:22222 ps

Attach to one of the containers and ping the other:

    docker -H tcp://$MANAGER_HOST_IP:22222 attach u1
    ping -w3 u2

#### Example 2: running [Memcached](http://cloudsuite.ch/datacaching/) containers with Swarm and swarm-network

In this example, we will run the simplest deployment of CloudSuite's Data Caching benchmark (i.e., a single client and a single server),
where each container runs on a different host. Let's assume we run the Memcached server on node n1 and the Memcached client on node n2.
We assume that the respective Docker images have been pulled to their respective hosts.

Run the server container on n1:

    docker -H tcp://$MANAGER_HOST_IP:22222 run --net swarm-network -e constraint:node==n1 --name dc-server -d cloudsuite/data-caching:server -t 4 -m 4096 -n 550

Run the client container on n2:

    docker run -it --net swarm-network --name dc-client cloudsuite/data-caching:client bash

Prepare the client and run its traffic generator, as described [here](http://cloudsuite.ch/datacaching/#preparing-the-client).

#### Setup environment for Swarm

To avoid typing `-H tcp://$MANAGER_HOST_IP:22222` with every docker command you
can:

    export DOCKER_HOST=tcp://$MANAGER_HOST_IP:22222
