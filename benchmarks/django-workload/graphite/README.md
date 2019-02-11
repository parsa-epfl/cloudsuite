# Monitoring configuration


## Build Graphite Image
Navigate to *cloudsuite/benchmarks/django-workload/graphite* and run:
        ```
        $ ./build_graphite.sh
        ```

## Run Graphite Container
Once you build the graphite image, run the container using:
        ```
        $ ./run_graphite.sh
        ```

## Mandatory config
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
