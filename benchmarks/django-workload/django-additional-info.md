# Additional information of each component in Django Workload

## Cassandra configuration

### Cassandra Docker status/validation
To check the status of cassandra container, execute the below script on the cassandra host machine
```
cloudsuite/benchmarks/django-workload/check_containers.sh <cassandra-host-private-ip> 9042 cassandra
```

### Troubleshoot
There seems to be an issue with cassandra expecting the output of `hostname` to
resolve to either an ipv4 address or to localhost; if you see an error about
`Local host name unknown: java.net.UnknownHostException` in
`/var/log/cassandra/system.log`, add `hostname` to `/etc/hosts` for the
`cassandra-host-private-ip` entry.

### Performance configuration
In order to increase the performance of your Cassandra deployment, the
following parameters in `/etc/cassandra/cassandra.yaml` can be changed:
```
concurrent_reads: 64
concurrent_writes: 128
concurrent_counter_writes: 128
```
The values above are suitable for a 2-socket Broadwell-EP server with 22 cores
per socket, with all services running on the same machine (Memcached, Cassandra,
uWSGI). These values might need to be changed depending on the platform.

Because the `concurrent_materialized_view_writes` feature is not necessary for
the Django Workload, it should be commented out:
```
#concurrent_materialized_view_writes: 32
```
The JVM settings can also be manually adjusted for better performance, by using the
following options in `/etc/cassandra/jvm.options`:
```
-XX:+UseThreadPriorities
-XX:ThreadPriorityPolicy=42

# Heap size (Xms, Xmx) and young generation size (Xmn) should be set depending
# on the amount of available memory. These settings work for a memory size of 8GB
-Xms4M
-Xmx4M
-Xmn1M

-XX:+HeapDumpOnOutOfMemoryError
-Xss256k
-XX:StringTableSize=1000003

# CMS settings
-XX:+UseParNewGC
-XX:+UseConcMarkSweepGC
-XX:+CMSParallelRemarkEnabled
-XX:SurvivorRatio=4
-XX:MaxTenuringThreshold=1
-XX:CMSInitiatingOccupancyFraction=60
-XX:+UseCMSInitiatingOccupancyOnly

-XX:+CMSScavengeBeforeRemark
-XX:CMSMaxAbortablePrecleanTime=60000

-XX:CMSWaitDuration=30000
-XX:+CMSParallelInitialMarkEnabled
-XX:+CMSEdenChunksRecordAlways
-XX:+CMSClassUnloadingEnabled

# Additional settings
-XX:+UseCondCardMark
-XX:MaxTenuringThreshold=2
-XX:-UseBiasedLocking
-XX:+UseTLAB
-XX:+ResizeTLAB
-XX:+PerfDisableSharedMem
-XX:+AlwaysPreTouch
-XX:+UnlockDiagnosticVMOptions
-XX:ParGCCardsPerStrideChunk=4096
```

## Memcached configuration
The cloudsuite/memcached-webtier docker sets up a memcached server with 5GB memory; you'll need a server or VM with that amount of memory.
The server binds to all network interfaces so this should only be run in a firewalled environment.

### Memcached Docker status/validation
To check the status of memcached container, execute the below script on the memcached host machine
```
cloudsuite/benchmarks/django-workload/check_containers.sh <memcached-host-private-ip> 11211 memcached
```

## Graphite configuration

### Graphite Docker status/validation
To check the status of Graphite container, execute the below script on the Graphite host machine
```
cloudsuite/benchmarks/django-workload/check_containers.sh <graphite-host-private-ip> 80 graphite
```

### Mandatory config
The default config of the hopsoft/graphite-statsd docker container will likely
cause your storage space to run out because of the amount of data being logged
into statsd by the Django Workload. In order to solve this, please perform the
steps below after starting the container. All commands should be run as root.

Obtain a shell in the container:
```
sudo docker exec -it graphite bash
cd opt/graphite/conf/
```

Add the following line to `blacklist.conf`:
```
^stats[^.]*\.benchmarkoutput\.
```

Edit the `carbon.conf` file to enable whitelisting. Search for the line
containing `USE_WHITELIST`, uncomment it and set it to True:
```
USE_WHITELIST = True
```

Edit the retention policy in `storage-schemas.conf`:
```
[default_1min_for_1day]
pattern = .*
retentions = 10s:2h,1min:2d,10min:14d
```

Exit the docker container and restart it for the configurations to take effect:
```
exit
docker stop graphite
docker start graphite
```

Should the disk space fill up again, you can simply delete graphite’s database:
```
docker exec -it graphite bash
rm –rf /opt/graphite/storage/whisper/*
```

## uWSGI configuration

### uWSGI Docker status/validation
To check the status of uWSGI container, execute the below script on the uWSGI host machine
```
cloudsuite/benchmarks/django-workload/check_containers.sh <uWSGI-host-private-ip> 8000 uwsgi
```

### Build uWSGI Image with custom Python build

        $ ./build_uwsgi.sh [/absolute/path/to/cpython/install]

### Debug

#### uWSGI Logging
Logging for uWSGI is turned off by default for benchmarking purposes. In order
to turn it back on, comment out the `disable-logging` parameter in uwsgi.ini,
For example:
```
#disable-logging = True
```

#### Django debugging
If you get HTTP response codes different than 200, change the DEBUG parameter
in cluster_settings.py (You can change the setting under uwsgi/files/django-workload/cluster_settings_template.py)
and rebuild the docker image:

    DEBUG = True

Then restart the uwsgi instance and go back to the page causing trouble. One of
the most common problems that cause 400 codes is not having the correct host in
the ALLOWED_HOSTS list in cluster_settings.py. If accessing the web server from
a different machine, add the hostname/IP address you are using to access the
server to ALLOWED_HOSTS.

## Siege configuration
Siege client will be used to stress test or benchmark the Python Django Workload.
This will install Siege version 4.1.3. There are no known version requirements
for Siege at the moment, although older versions might not allow concurrency
levels higher than 256, which can be insufficient to properly stress a certain
system.

### Configure Siege for your deployment
Siege uses a urls.txt file to know where to direct the requests. This file can
be regenerated by modifying the urls_template.txt to point to different pages
or to assign new weigths to your urls (some pages are accessed more than
others).

The default urls.txt points to localhost:8000 and uses URL weights that follow
real life usage we observed.

Use the ./gen-urls-file to generate a new urls.txt from the urls_template.txt:

    ./gen-urls-file

You must have Python 3 installed to run the above script.

### Run siege
Run siege using the ./run-siege script:

    ./run-siege

This script can be configured to suit your needs by altering the following
environment variables that change the Siege parameters:

    WORKERS     - specifies the number of concurrent Siege workers
    DURATION    - specifies the run time of the benchmark, in the format "XY",
                  where X is a number and Y is the time unit (H, M or S, for
                  hours, minutes or seconds)
    LOG         - specifies the log file for Siege
    SOURCE      - specifies the input file that tells Siege what urls to
                  benchmark

The command above that launches the Siege client is equivalent to the command
below, where the configuration parameters have the default value:

    WORKERS=144 DURATION=2M LOG=./siege.log SOURCE=urls.txt ./run-siege

### Useful system configurations
Running siege will possibly throw the following error:

    [error] socket: 1384314624 address is unavailable.: Cannot assign requested
            address

This is due to reaching OS limits for open file descriptors (sockets in this
case). In order to prevent this, please perform the following steps:

    1. Modify /etc/sysctl.conf by adding the following lines:
    ...
    net.ipv4.tcp_tw_reuse=1
    net.ipv4.ip_local_port_range=1024 64000
    net.ipv4.tcp_fin_timeout=45
    net.core.netdev_max_backlog=10000
    net.ipv4.tcp_max_syn_backlog=12048
    net.core.somaxconn=1024
    net.netfilter.nf_conntrack_max = 256000
    ...

    #then apply
    sudo sysctl -f

    The nf_conntrack module might need to be loaded:

        sudo modprobe nf_conntrack

    To make it persistent, add it to /etc/modules.

    2. Set open files to 1 milion
    sudo vim /etc/security/limits.conf
    # insert the following lines at the end of this file
    * soft nofile 1000000
    * hard nofile 1000000

    # check original values as this example ouput:
    ulimit -n
    1024

    # reboot system and check the values again:
    ulimit –n
    1000000
    # the “open files” value is now set at 1,000,000

Sometimes siege will abort the run if it reaches its own internal error
threshold. This threshold can be adjusted in the ~/.siegerc file (may need to
be created) using the following attribute:

    failures = 1000000

When running all the services on a single machine, it is also possible to hit
the PID limit for the current user, resulting in Siege errors like:

    [error] Inadequate resources to create pool crew.c:87: Resource temporarily unavailable
    [fatal] unable to allocate memory for 185 simulated browser: Resource temporarily unavailable

When this error appears, you will not be able to open another terminal:

    -bash: fork: retry: Resource temporarily unavailable

Solving this requires setting a large enough value for the systemd UserTasksMax
variable:

    sudo vim /etc/systemd/logind.conf
    [Login]
    # insert the following line under the Login attribute
    UserTasksMax=1000000

Reboot for the changes to take effect.
