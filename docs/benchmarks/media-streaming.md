# Media Streaming

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses the [Nginx][nginx_repo] web server as a streaming server for hosting videos of various lengths and qualities. Based on [videoperf][httperf_repo]'s session generator, the client requests different videos to stress the server.

## Using the Benchmark ##
The benchmark has two tiers: the server and the client. The server runs Nginx, and the client requests videos. Each tier has its own image, identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`server`][serverdocker] contains the Nginx streaming server running as a daemon.
 - [`client`][clientdocker] contains the `videoperf` client.
 - [`dataset`][datasetdocker] provides the video dataset for the streaming server.

### Running Dataset and Server

The dataset image has two purposes. First, it generates video files with different resolutions (from 240p to 720p) for the server docker container. Then, based on the generated videos, it suggests request lists for the client docker container. 

Use the following command to run the dataset container:

```bash
$ docker run --name streaming_dataset cloudsuite/media-streaming:dataset ${DATASET_SIZE} ${SESSION_COUNT}
```

The parameter `DATASET_SIZE`, in GBs, scales the size of the dataset to the given number. By default, the dataset container generates ten videos for each of 240p, 360p, 480p, and 720p resolutions, resulting in 3.5 GB size in total. 

The parameter `SESSION_COUNT` denotes the number of sessions for requesting video files. For each resolution, the dataset container generates a list of sessions (named `session lists`) to guide the client on how to stress the server. By default, the value is five. 

In `videoperf`'s context, a `session` is a sequence of HTTP/HTTPS queries to fetch a specific video chunk. As a reference, you can find a sample session below:

```
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=0-524287'
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=524288-1048575'
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=1048576-1572863'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=1572864-2097151'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=2097152-2621439'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=2621440-3145727'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=3145728-3221180'
```
Each line here defines an HTTP/HTTPS query with the following fields:
- The name of the requested video. In our example, all queries ask for `/full-240p-00004.mp4`.
- `timeout` determines the maximum time the client waits to receive any response after sending a query to the server. Once expired, the client closes the connection and increases the timeout counter. 
- `pace_time` determines the latency between sending consecutive queries. By default, the first three queries of each session don't have this field, which means they are sent together. 
- `header` declares a range of video bytes for each query. 

It is possible that sessions in the `session lists` don't touch the whole dataset. In this case, consider increasing the `SESSION_COUNT`. Check the beginning of the `session lists` for each resolution to see the related statistics.

### Starting the Server ####
Start the server on the same machine as the dataset container: 

```bash
$ docker run -d --name streaming_server --volumes-from streaming_dataset --net host cloudsuite/media-streaming:server ${NGINX_WORKERS}
```

The `NGINX_WORKERS` parameter sets the number of Nginx workers. If not given, the default value is 2000. Adjust this number based on the server's computational resources and the intended load.

### Starting the Client ###

You need to copy the `session lists` from the dataset container and then transfer them to the server where you want to launch the `videoperf` client. 

To copy `session lists` from the dataset container to server, use the following command:

```bash
$ docker cp streaming_dataset:/videos/logs <destination>
```

Then, you can use any command (e.g., `scp`, `rsync`) to transfer files to the client machine. 

To run the client container, use the following command:

```bash
$ docker run -t --name=streaming_client -v <lists>:/videos/logs -v <results>:/output --net host cloudsuite/media-streaming:client ${SERVER_IP} ${VIDEOPERF_PROCESSES} ${VIDEO_COUNT} ${RATE} ${ENCRYPTION_MODE}
```

Parameters are:
- `<lists>`: The path where the `session lists` is put. You should be able to find files like `cl-*.log`
- `<results>`: The path for the benchmark statistics files. 
- `SERVER_IP`: The IP address of the server. 
- `VIDEOPERF_PROCESSES`: The number of videoperf processes, with a default value of 4. 
- `VIDEO_COUNT`: The total number of videos to request. Each video is represented by one session from the `session list`, and the client requests the video by sending HTTP queries to get video chunks sequentially. 
- `RATE`: The rate (videos per second) for new video request generation. 
- `ENCRYPTION_MODE`: Whether the transfer is encrypted or not. Possible values are "PT", which stands for plain text; and "TLS", which enables TLS v1.3.

#### Note for Video Request Generation

Video requests are distributed equally among different videoperf processes to balance the load. For example, if you have 1000 videos to request and 5 videoperf processes, each process will handle 200 videos. Then, each videoperf process further distributes its videos among different resolutions by 10%, 30%, 40%, and 20% probabilities for 240p, 360p, 480p, and 720p, respectively. You might want to change these probabilities based on your need by modifying [this](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/files/run/peak_hunter/launch_remote.sh) script. 

If the number of required videos for a specific resolution is larger than the number of sessions in its `session list`, the client container reuses the list and starts from the beginning of the `session list`. 

Some load generators implement a thread pool and assign each video request to a thread. On the contrary, videoperf is a single-thread process. Its programming model is based on scheduling timers, which call the corresponding function once it expires. For example, there is a [periodic timer](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/files/videoperf/gen/rate.c#L132) that is set based on the `RATE` parameter and is used to generate new video requests. 

Videoperf represents an open-loop load generator that sends subsequent requests independent of the server's responses to the previous requests. 

### Guidelines for Tuning the Benchmark

After running the benchmark, the client container periodically reports three metrics:
- Throughput in Mbps
- The total number of errors encountered during the benchmark's execution
- The number of concurrent established requests to the server
- The reply rate, which is the number of HTTP requests finished per second

A sample report looks like this:
```
Throughput (Mbps) = 465.59  , total-errors = 0       , concurrent-clients = 161     , reply-rate = 17.6
```
Note that each videoperf process reports its statistics. Therefore, the overall state of the benchmark will be the sum of individual reports. 

To tune the benchmark, give a starting rate as the seed (we suggest 1). The benchmark will take a few minutes to reach a steady state. Consequently, consider giving an appropriate number for `VIDEO_COUNT`. For example, if the benchmark did not reach steady state in 5 minutes and the `RATE` was ten video requests per second, the number of requested videos need to be larger than 5x60x10=3000. Therefore, we suggest giving a large value to `VIDEO_COUNT` to sustain a long steady state. 

Other points to consider are:
1. The benchmark reaches a steady state when both throughput and the number of concurrent video requests are stable while there are few encountered errors. The number of errors should be 0, but occasional errors may occur. 
2. If there is a problem in the tuning process, the number of errors will increase rapidly. 
3. In the ramp-up phase, both throughput and the number of concurrent video requests will increase. The throughput may become stable, but the number of concurrent video requests can continue to increase. It means that the rate of establishing new video requests is higher than the server's capabilities. In this case, consider decreasing the `RATE` parameter of the client container.
4. If you find the benchmark is in a steady state, you might want to increase the `RATE` parameter to see whether the server can handle a higher load.
5. Remember that videoperf is a highly demanding single-thread process. Therefore, we recommend that you ensure the number of available cores for the client container is higher than or equal to the number of videoperf processes. 


[datasetdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/dataset/Dockerfile "Dataset Dockerfile"  

[serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/server/Dockerfile "Server Dockerfile"

[clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/Dockerfile "Client Dockerfile"

[repo]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/media-streaming "GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/media-streaming/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/media-streaming.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/media-streaming.svg "Go to DockerHub Page"
[nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
[httperf_repo]: https://github.com/httperf/httperf "httperf repo"
