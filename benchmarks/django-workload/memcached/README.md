# Memcached configuration

## Requirements
This directory sets up a memcached server with 5GB memory; you'll need a server
or VM with that amount of memory.

The server binds to *all network interfaces* so this should only be run in a
firewalled environment.

## Build Memcached Base Imgae
First, create memcached base image as follows by navigating to *cloudsuite/commons/memcached* directory and run:
	```
	$ docker build . --tag cloudsuite/memcached
	```

## Build Memcached Image
Navigate to *cloudsuite/benchmarks/django-workload/memcached* and run:
	```
	$ ./build_memcached.sh
	```

## Run Memcached Container
Once you build the memcached image, run the container using:
	```
	$ ./run_memcached.sh
	```
