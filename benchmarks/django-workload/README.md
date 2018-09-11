# Docker containers setup files

This directory contains all docker files and all necessary dependencies to
build and deploy all the docker images necessary to run the Django
Workload. Each entity (Cassandra, uWSGI, Memcached, Siege, Graphite) is set up
in a separate container.

For instructions on how to install docker, please refer to:
<https://docs.docker.com/engine/installation/linux/ubuntu/>

## Warning

The Cassandra heap size is set to 64GB in
[cloudsuite/commons/cassandra/files/jvm.options.128_GB](cloudsuite/commons/cassandra/files/jvm.options.128_GB).
If your machine does not have that much RAM, starting the Cassandra container
will cause swapping, therefore your machine will become unresponsive.

Please change the value of the heap size in the file mentioned above to a more
suitable value (change Xms and Xmx to half the system memory or less). Also
change Xmn proportionally to the previous heap size (if changing heap size to
1/4 its original value, also reduce Xmn to 1/4 its original value).


## Build the docker images
To build cassandra image, go to the cloudsuite/commons/cassandra folder location, and run
	docker build --no-cache -t cassandra-webtier .

To build memcached image, go to the cloudsuite/commons/memcached folder location, and run
	docker build --no-cache -t memcached-webtier .

To build uwsgi, graphite and siege images for the workload, run

    [UWSGI_ONLY=1] ./build_containers.sh [/absolute/path/to/installed/python]

Running the above script with no parameters will deploy the system Python 3.5.2
on the uWSGI container. In order to deploy a custom Python build, please
provide the script above with the absolute path to the install folder of your
build

    # CPython tree
    ./configure --prefix=/python/install/folder
    make
    make install
    # Docker scripts
    ./build_containers.sh /python/install/folder

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

In order to build only the uWSGI image, UWSGI_ONLY=1 must be specified before
the build_containers.sh script:

    # remember to remove the old container & image
    ./cleanup_containers.sh
    docker image rm uwsgi-webtier
    UWSGI_ONLY=1 ./build_containers.sh /some/folder

By default, docker commands will require root access. If using "sudo", remember
to specify the "UWSGI_ONLY=1" variable __after__ the "sudo" word, otherwise it
will not be taken into consideration.

To run docker without "sudo", please follow the instructions here:
<https://docs.docker.com/engine/installation/linux/linux-postinstall/>

# Run the workload

Simply run:

    # default number of Siege workers is 185. This can be
    # changed using the WORKERS environment variable
    [WORKERS=185] ./run_containers.sh

# Cleanup containers

In order to do another run, the previous containers need to be removed:

    ./cleanup_containers.sh
