# Media Streaming

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses the [Nginx][nginx_repo] web server as a streaming server for hosted videos of various lengths and qualities. Based on [videoperf][httperf_repo]'s session generator, the client requests different videos to stress the server.

## Using the Benchmark ##
The benchmark has two tiers: the server and the client. The server runs Nginx, and the client sends requests to stream videos. Each tier has its image, which is identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`server`][serverdocker]: This image represents the Nginx streaming server running as a daemon.
 - [`client`][clientdocker]: This image represents the `videoperf` client.
 - [`dataset`][datasetdocker]: This image provides the video dataset for the streaming server.

### Running Dataset and Server on Host1

The dataset image has two purposes. First, it generates video files with different resolutions (from 240p to 720p) for the server docker container. 
Then, based on the generated videos, it suggests the request lists for the client docker container. 

First, use the following command to pull the dataset image:

    $ docker pull cloudsuite/media-streaming:dataset

Then, use the following command to run the dataset container:

    $ docker run --name streaming_dataset cloudsuite/media-streaming:dataset ${DATASET_SIZE} ${SESSION_COUNT}
    
`DATASET_SIZE` in GBs, scales the size of the dataset to the given number. By default, the dataset container generates ten videos for each of 240p, 360p, 480p, and 720p resolutions, having around 3.5 GB size altogether. 

`SESSION_COUNT` denotes the number of sessions to stream the video files. For every resolution, the dataset container generates a list of sessions (named `session lists`) to guide the client on how to stress the server. By default, the value is five. 

In `videoperf`'s context, a `session` is a sequence of HTTP/HTTPS requests to fetch a specific video chunk. As a reference, you can find a sample session below:

```
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=0-524287'
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=524288-1048575'
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=1048576-1572863'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=1572864-2097151'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=2097152-2621439'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=2621440-3145727'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=3145728-3221180'
```
Each line here defines an HTTP/HTTPS request with the following fields:
- The name of the requested video. In our example, requests ask for `/full-240p-00004.mp4`.
- `timeout` determines the maximum time the client waits before receiving any response after sending the request to the server. Once expired, the client closes the connections and increases the timeout counter. 
- `pace_time` determines the latency between sending two consecutive requests. By default, the first three requests of each session don't have this field, which means they are sent together. 
- `header` declares a range of video bytes for each request. 

It is possible that sessions in the `session lists` don't touch the whole dataset. In this case, consider increasing the `SESSION_COUNT`. Check the beginning of the `session lists` for each resolution to see the related statistics.

### Starting the Server on Host1 ####
Start the server on the same machine as the dataset container: 

    $ docker run -d --name streaming_server --volumes-from streaming_dataset --net host cloudsuite/media-streaming:server ${NGINX_WORKERS}

The `NGINX_WORKERS` parameter sets the number of Nginx workers. If not given, the default value is 2000. Adjust this number based on the server's computational resources and the intended load.

### Starting the Client on Host2 ###

You need to copy the `session lists` from the dataset container and then transfer the files to Host2, where you want to launch the `videoperf` client. 

To copy `session lists` from the dataset container to Host 1, use the following command:

    $ docker cp streaming_dataset:/videos/logs <destination>

Then, you can use any command (e.g., `scp`, `rsync`) to transfer files to Host2. 

To run the client container, use the following command:

    $ docker run -t --name=streaming_client -v <lists>:/videos/logs -v <results>:/output --net host cloudsuite/media-streaming:client ${SERVER_IP} ${VIDEOPERF_PROCESSES} ${VIDEO_COUNT} ${RATE} ${ENCRYPTION_MODE}

Parameters are:
- `<lists>`: The path where the `session lists` is put. You should be able to find files like `cl-*.log`
- `<results>`: The path for the benchmark statistics files. 
- `SERVER_IP`: The IP address of the server, which should be the Host1 in this document. 
- `VIDEOPERF_PROCESSES`: The number of videoperf processes, with a default value of 4. 
- `VIDEO_COUNT`: The total number of videos to stream. Each video requesting will use one session from the `session list` and send the corresponding requests. 
- `RATE`: The rate (videos per second) for new video request generation. 
- `ENCRYPTION_MODE`: Whether the transfer is encrypted or not. Possible values are "PT", which stands for plain text; and "TLS", which enables TLS v1.3.

#### Note for Video Request Generation

Video streaming requests are distributed equally among different videoperf processes to balance the load. For example, if you have 1000 videos to stream and 5 videoperf processes, each process will handle 200 videos. Then, each videoperf process further distributes its videos among different resolutions by 10%, 30%, 40%, and 20% probabilities for 240p, 360p, 480p, and 720p, respectively. You might want to change these probabilities based on your need by modifying [this](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/files/run/peak_hunter/launch_remote.sh) script. 

If the number of required videos for a specific resolution is larger than the number of sessions in its `session list`, the client container reuses the list and starts from the beginning of the `session list`. 

Some load generators implement a thread pool and assign each fetching request to a thread. On the contrary, videoperf is a single-thread process. Its programming model is based on scheduling timers, which call the corresponding function once it expires. For example, there is a [periodic timer](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/files/videoperf/gen/rate.c#L132) that is set based on the `RATE` parameter and is used to generate new clients. 

Videoperf represents an open-loop load generator that sends subsequent requests independent of the server's responses to the previous requests. 

### Guidelines for Tuning the Benchmark

After running the benchmark, the client container periodically reports three metrics:
- Throughput in Mbps
- The total number of errors encountered during the benchmark's execution
- The number of concurrent established requests to the server
- The reply rate, which is thenumber of HTTP requests finished per second

A sample report looks like this:
```
Throughput (Mbps) = 1325.97 , total_errors = 0       , concurrent-clients = 317       ,reply-rate=22.3,
```
Note that each videoperf process reports its statistics. Therefore, the overall state of the benchmark will be the sum of individual reports. 

To tune the benchmark, give a starting rate as the seed (we suggest 1). The benchmark would take a few minutes to reach a steady state. Consequently, consider giving an appropriate number for `VIDEO_COUNT`. For example, if the benchmark did not reach steady in 5 minutes and the `RATE` was ten clients per second, the number of streaming videos would be larger than 5x60x10=3000. Therefore, we suggest giving a large value to `VIDEO_COUNT` to sustain a long steady state. 

Other principles are:
1. The benchmark reaches the steady state when both throughput and concurrent clients are stable while there are few encountered errors. The number of errors would be 0, but occasional errors may occur. 
2. If there is a problem in the tuning process, the number of errors will increase rapidly. 
3. In the ramp-up phase, both throughput and concurrent clients will be increasing. The throughput may be stable, but concurrent clients continue to increase. It means that the rate of establishing new clients is higher than the server's capabilities. In this case, consider decreasing the `RATE` parameter of the client container.
4. If you find the benchmark in a steady state, you might want to increase the `RATE` to see whether the server can handle a higher load.
5. An overloaded client container would result in errors and crashes. In this case, consider allocating more cores to support more videoperf processes. You can check the client container's CPU utilization using different tools (e.g., docker stats) and compare it against the number of cores on the client machine or the number of cores devoted to the container by docker (e.g., by --cpuset-cpus option). 
6. Remember that videoperf is a highly demanding single-thread process. Therefore, we recommend that you ensure the number of available cores for the client container is higher or equal to the number of videoperf processes. 


[datasetdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/dataset/Dockerfile "Dataset Dockerfile"  

[serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/server/Dockerfile "Server Dockerfile"

[clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/Dockerfile "Client Dockerfile"

[repo]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/media-streaming "GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/media-streaming/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/media-streaming.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/media-streaming.svg "Go to DockerHub Page"
[nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
[httperf_repo]: https://github.com/httperf/httperf "httperf repo"
