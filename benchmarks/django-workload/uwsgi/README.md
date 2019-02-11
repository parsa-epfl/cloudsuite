# The Django server and uWSGI


## Build uWSGI Image
Navigate to *cloudsuite/benchmarks/django-workload/uwsgi* and choose one of the ways to build the *uwsgi* image:
1. With default Python build
        ```
        $ ./build_uwsgi.sh
        ```

2. With custom Python build
        ```
        $ ./build_uwsgi.sh [/absolute/path/to/cpython/install]
        ```

## Run uWSGI Container
Configure the endpoints of *Graphite, Cassandra, Memcached and Siege* in the **uwsgi.cfg** file base on the setup.

*Example*:
```
GRAPHITE_ENDPOINT=localhost
CASSANDRA_ENDPOINT=localhost
MEMCACHED_ENDPOINT="localhost:11211"
SIEGE_ENDPOINT=localhost
```

Once you configured the endpoints, run the container using:
        ```
        $ ./run_uwsgi.sh
        ```

## Debug

### uWSGI Logging
Logging for uWSGI is turned off by default for benchmarking purposes. In order
to turn it back on, comment out the `disable-logging` parameter in uwsgi.ini,
like this:
```
#disable-logging = True
```

### Django debugging
If you get HTTP response codes different than 200, change the DEBUG parameter
in cluster_settings.py:

    DEBUG = True

Then restart the uwsgi instance and go back to the page causing trouble. One of
the most common problems that cause 400 codes is not having the correct host in
the ALLOWED_HOSTS list in cluster_settings.py. If accessing the web server from
a different machine, add the hostname/IP address you are using to access the
server to ALLOWED_HOSTS.
