# Django workload by Instagram and Intel, v1.0 RC

This project aims to provide a Django workload based on a real-world
large-scale production workload that serves mobile clients.

## Setup for Docker Containers

The workload can be deployed using Docker containers to gauge the impact of the Django
workload on both Python and the hardware it runs on.

The below services are needed for the workload
* 3 services
  * Cassandra - [cassandra/README.md](#cassandra-configuration)
  * Memcached - [memcached/README.md](#memcached-configuration)
  * Graphite (for monitoring) - [graphite/README.md](#graphite-configuration)

* Django and uWSGI server - [uwsgi/README.md](#uwsgi-configuration)
* Siege client (a load generator) - [siege/README.md](#siege-configuration)


# Running benchmark on single/multiple VMs

## Run Memcached on Host 1
	$ docker pull cloudsuite/django-workload:memcached
	$ docker run -tid --name memcached_container --network host cloudsuite/django-workload:memcached

## Run Cassandra on Host 2
	$ docker pull cloudsuite/django-workload:cassandra
	$ docker run -tid --name cassandra_container -e SYSTEM_MEMORY=8 -e ENDPOINT=<cassandra-host-private-ip> --network host cloudsuite/django-workload:cassandra

	# SYSTEM_MEMORY : for cassandra in GB (example: 8)
	# ENDPOINT : cassandra-host-private-ip (example: 192.168.XXX.XXX)

## Run Graphite on Host 3
	$ docker pull cloudsuite/django-workload:graphite
	$ docker run -tid --name graphite_container --network host cloudsuite/django-workload:graphite

## Run uwsgi on Host 4
        $ docker pull cloudsuite/django-workload:uwsgi
        
        # Edit uwsgi.cfg with endpoints (host-private-ip) of uWSGI, Graphite, Cassandra, Memcached and Seige

	$ cd cloudsuite/benchmarks/django-workload/uwsgi/
        $ . ./uwsgi.cfg
        $ docker run -tid --name uwsgi_container --network host -e GRAPHITE_ENDPOINT=$GRAPHITE_ENDPOINT -e CASSANDRA_ENDPOINT=$CASSANDRA_ENDPOINT -e MEMCACHED_ENDPOINT="$MEMCACHED_ENDPOINT" -e SIEGE_ENDPOINT=$SIEGE_ENDPOINT -e UWSGI_ENDPOINT=$UWSGI_ENDPOINT cloudsuite/django-workload:uwsgi

## Run siege on Host 5
        $ docker pull cloudsuite/django-workload:siege

        # Edit siege.cfg withthe endpoint of uWSGI and the number of siege workers needed 

        $ cd cloudsuite/benchmarks/django-workload/siege/
        $ . ./siege.cfg
        $ docker run -ti --name siege_container --volume=/tmp:/tmp --network host -e TARGET_ENDPOINT=$UWSGI_ENDPOINT -e SIEGE_WORKERS=$SIEGE_WORKERS cloudsuite/django-workload:siege


The workload can be run on a single VM, by running all the above docker containers on a single machine. You can use host-private-ip for all the endpoints.

Additional information of each component can be found [here](../../benchmarks/django-workload/django-additional-info.md)

