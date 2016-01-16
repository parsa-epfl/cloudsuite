---
layout: page
title: CloudSuite on Docker Demo
---

This demo illustrates the use of CloudSuite's Data Caching benchmark on Docker.

#### Prerequisites

To follow this demo, you need to have Docker version 1.9.0 or newer installed on your machine.
Since the size of the images for the Data Caching workload that will be used is several megabytes, it is recommended
that you download them in advance:

    $ docker pull cloudsuite/data-caching:server
    $ docker pull cloudsuite/data-caching:client

#### Single-node setup and run

We will go through the setup and execution process of a single server - single client Memcached deployment on a single host machine.
The instructions to do that can be found [here](http://cloudsuite.ch/datacaching/).

#### Multinode setup and run

We will illustrate how we can run Docker containers on different hosts and have them communicate, by building an overlay network
using [Docker Swarm](https://docs.docker.com/swarm/). We will illustrate the multinode setup and use by reusing the Memcached
example. The instructions for that can be found [here](../swarm).
