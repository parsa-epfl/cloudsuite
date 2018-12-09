# Cassandra configuration

## Build Cassandra Base Image
First, create cassandra base image as follows by navigating to *cloudsuite/commons/cassandra* directory and run:
	```
	$ docker build . --tag cloudsuite/cassandra
	```

## Build Cassandra Image
Navigate to *cloudsuite/benchmarks/django-workload/cassandra* and run:
        ```
        $ ./build_cassandra.sh
        ```

## Run Cassandra Container
Once you build the cassandra image, run the container using:
        ```
        $ ./run_cassandra.sh
	```
## Troubleshoot
To change the default listen address for Cassandra (localhost), edit
`/etc/cassandra/cassandra.yaml` to replace the `listen_address` configuration
with `listen_address <desired_ip_address>`. If planning to deploy Cassandra
on the same machine as uWSGI, this parameter does not need changing.

There seems to be an issue with cassandra expecting the output of `hostname` to
resolve to either an ipv4 address or to localhost; if you see an error about
`Local host name unknown: java.net.UnknownHostException` in
`/var/log/cassandra/system.log`, add `hostname` to `/etc/hosts` for the
`127.0.0.1` entry.

## Performance configuration
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
The JVM settings can also be adjusted for better performance, by using the
following options in `/etc/cassandra/jvm.options`:
```
-XX:+UseThreadPriorities
-XX:ThreadPriorityPolicy=42

# Heap size (Xms, Xmx) and young generation size (Xmn) should be set depending
# on the amount of available memory. These settings work for a memory size of 128GB
-Xms65536M
-Xmx65536M
-Xmn16384M

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

## Logging
If needed, logging can be enabled for Cassandra by adding the following flags
to `/etc/cassandra/jvm.options`:
```
# Logging
-XX:+PrintGCDetails
-XX:+PrintGCDateStamps
-XX:+PrintTenuringDistribution
-XX:+PrintGCApplicationStoppedTime
-XX:+PrintPromotionFailure
-XX:+PrintClassHistogramBeforeFullGC
-XX:+PrintClassHistogramAfterFullGC
-Xloggc:/var/log/cassandra/gc.log
-XX:+UseGCLogFileRotation
-XX:NumberOfGCLogFiles=2
-XX:GCLogFileSize=10M
```
