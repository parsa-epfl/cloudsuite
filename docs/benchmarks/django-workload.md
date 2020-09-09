# Django workload by Instagram and Intel, v1.0 RC

This project aims to provide a Django workload based on a real-world
large-scale production workload that serves mobile clients.

## Setup for Docker Containers

The workload can be deployed using Docker containers to gauge the impact of the Django
workload on both Python and the hardware it runs on.

Documentation to set up each component (possibly a multinode setup)is provided each subdirectory. 
You'll need to follow the **README.md** file in each of the
following locations:

* 3 services
  * Cassandra - [cassandra/README.md](/benchmarks/django-workload/cassandra/README.md)
  * Memcached - [memcached/README.md](/benchmarks/django-workload/memcached/README.md)
  * Graphite (for monitoring) - [graphite/README.md](/benchmarks/django-workload/graphite/README.md)

* Django and uWSGI server - [uwsgi/README.md](/benchmarks/django-workload/uwsgi/README.md)
* Siege client (a load generator) - [siege/README.md](/benchmarks/django-workload/siege/README.md)

# Note
You could run build_containers.sh followed by run_containers.sh inorder to setup a the workload on a single machine.
Usage of build_containers.sh:
	```
	$ [UWSGI_ONLY=1] ./build_containers.sh [/absolute/path/to/installed/python]
	```
Here, UWSGI_ONLY=1 & /absolute/path/to/installed/python are optional parameters. They could be used in following scenarios:
1. Default: Builds all the components.
	```
	$ ./build_containers.sh
	```
2. Build only UWSGI server(which might be needed often when you want to tune server and rebuild the image)
	```
	$ UWSGI_ONLY=1 ./build_containers.sh
	```
3. In order to deploy a custom Python build on the UWSGI container, please provide the script above with the absolute path to the install folder of your build. By default running the script without this parameter will deploy the system Python 3.5.2. Since this workload uses modeling a realistic use case of Python in large web deployments and the workload reflects a typical web server application running on multiple server nodes with a client simulating the payloads of different request mixes, one would be interested in running the experiments with different versions of Python.
	```
	$ ./build_containers.sh /absolute/path/to/installed/python
	```

The root directory contains sub directories for each of these components which contains 
docker files and all necessary dependencies to build and deploy all the docker images necessary 
to run the Django Workload. Each entity (Cassandra, uWSGI, Memcached, Siege, Graphite) is set up
in a separate container.

4. For running all components on a single VM
	```
	$ ./run_containers.sh
	```

For instructions on how to install docker, please refer to:
<https://docs.docker.com/engine/installation/linux/ubuntu/>

# Running benchmark on multiple VMs

## Run Memcached on Host 1
	$ cd cloudsuite/benchmarks/django-workload/memcached/
	$ ./build_memcached.sh
	$ ./run_memcached.sh

## Run Cassandra on Host 2
	$ cd cloudsuite/benchmarks/django-workload/cassandra/
	$ ./build_cassandra.sh
	$ ./run_cassandra.sh 8 <cassandra-host-private-ip>

	# arg1: SYSTEM_MEMORY for cassandra in GB (example: 8)
	# arg2: cassandra-host-private-ip (example: 192.168.XXX.XXX)

## Run Graphite on Host 3
	$ cd cloudsuite/benchmarks/django-workload/graphite/
	$ ./build_graphite.sh
	$ ./run_graphite.sh

## Run uwsgi on Host 4
	$ cd cloudsuite/benchmarks/django-workload/uwsgi/
+ Edit uwsgi.cfg with endpoints of Graphite, Cassandra, Memcached and Seige
+ Edit files/django-workload/cluster_settings_template.py with endpoint of uwsgi host in ALLOWED_HOSTS (add uwsgi-private-host-ip to array in line 15)

```
$ ./build_uwsgi.sh
$ ./run_uwsgi.sh
```

## Run siege on Host 5
	$ cd cloudsuite/benchmarks/django-workload/siege/
+ Edit seige.cfg with endpoints of Uwsgi

```
$ ./build_siege.sh
$ ./run_siege.sh
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
