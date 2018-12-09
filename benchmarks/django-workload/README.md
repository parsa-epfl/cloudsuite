# Django workload by Instagram and Intel, v1.0 RC

This project aims to provide a Django workload based on a real-world
large-scale production workload that serves mobile clients.

## Setup for Docker Containers

The workload can be deployed using Docker containers to gauge the impact of the Django
workload on both Python and the hardware it runs on.

Documentation to set up each component is provided each subdirectory. 
You'll need to follow the **README.md** file in each of the
following locations:

* 3 services
  * Cassandra - [cassandra/README.md](/benchmarks/django-workload/cassandra/README.md)
  * Memcached - [memcached/README.md](/benchmarks/django-workload/memcached/README.md)
  * Graphite (for monitoring) - [graphite/README.md](/benchmarks/django-workload/graphite/README.md)

* Django and uWSGI server - [uwsgi/README.md](/benchmarks/django-workload/uwsgi/README.md)
* Siege client (a load generator) - [siege/README.md](/benchmarks/django-workload/siege/README.md)


The root directory contains sub directories for each of these components which contains 
docker files and all necessary dependencies to build and deploy all the docker images necessary 
to run the Django Workload. Each entity (Cassandra, uWSGI, Memcached, Siege, Graphite) is set up
in a separate container.

For instructions on how to install docker, please refer to:
<https://docs.docker.com/engine/installation/linux/ubuntu/>

## Warning

The Cassandra heap size is set to 64GB in
[cloudsuite/benchmarks/django-workload/cassandra/files/jvm.options.128_GB](/benchmarks/django-workload/cassandra/files/jvm.options.128_GB).
If your machine does not have that much RAM, starting the Cassandra container
will cause swapping, therefore your machine will become unresponsive.

Please change the value of the heap size in the file mentioned above to a more
suitable value (change Xms and Xmx to half the system memory or less). Also
change Xmn proportionally to the previous heap size (if changing heap size to
1/4 its original value, also reduce Xmn to 1/4 its original value).
*Example*: use on a system with 8GB memory
```
-Xms4G
-Xmx4G
-Xmn2G
```

# Note
Please note that the latest Python version that was used to test the
installation scripts was 3.6.3. Subsequent versions have not been tested. All
the packages installed for Docker are the same as what would be installed on
bare-metal if using Ubuntu 16.04. All Python dependencies installed via pip
that do not have a specific version requirement are subject to change. At the
time this documentation was written, the following package versions were used:
* Cassandra 3.0.14
* Memcached 1.4.25-2ubuntu1.2
* Java 8u151
* Siege 3.0.8
* gcc 5.4.0-6ubuntu1
* uWSGI 2.0.15

The first time the workload is deployed, all images need to be built. After
this, in order to measure the performance of a different Python build, only
the uWSGI image needs to be re-built. This is the only image that changes, as
the Python used for the workload changes.

To run docker without "sudo", please follow the instructions here:
<https://docs.docker.com/engine/installation/linux/linux-postinstall/>
