# Media Streaming

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses the [Nginx][nginx_repo] web server as a streaming server for hosted videos of various lengths and qualities. The client, based on [httperf][httperf_repo]'s `wsesslog` session generator, generates a request mix for different videos, to stress the server.

## Using the benchmark ##
The benchmark has two tiers: the server and the clients. The server runs Nginx, and the clients send requests to stream videos from the server. Each tier has its own image which is identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`server`][serverdocker]: This represents the Nginx streaming server running as a daemon.
 - [`client`][clientdocker]: This represents the `httperf` client.
 - [`dataset`][datasetdocker]: This represents the video files dataset for the streaming server.

These images are automatically built using the mentioned Dockerfiles available on the `parsa-epfl/cloudsuite` [GitHub repo][repo].

### Running Dataset and server on Host1

The streaming server requires a video dataset to serve. We generate a synthetic dataset, comprising several videos of different lengths and qualities. We provide a separate docker image that handles the dataset generation, which is then used to launch a dataset container that exposes a volume containing the video dataset.

To set up the dataset you have to first `pull` the dataset image and then run it. To `pull` the dataset image use the following command:

    $ docker pull cloudsuite/media-streaming:dataset

The following command will run the dataset container that exposes the video dataset volume, which will be used by the streaming server:

    $ docker run --name streaming_dataset cloudsuite/media-streaming:dataset ${DATASET_SIZE} ${SESSIONS_COUNT}

Copy logs folder from container to Host1:

    $ docker cp ${DATASET_CONTAINER_ID}:/videos/logs $HOME
    
`DATASET_SIZE`, in GBs, scales the size of the dataset to the given number. If you don't specify `DATASET_SIZE`, by default, the dataset container generates a collection of 10 videos for each of 240p, 360p, 480p, and 720p resolutions, having around 3.5 GB size altogether. 

For each of the resolutions, the dataset container creates a log file located in the `/videos/logs` folder that you already copied from the container to the host machine. Each log file holds the sessions. Each session represents the behavior of a specific client streaming a specific media by mentioning the name of the requested media and the bytes streamed in each connection. The log file separates sessions by blank lines. A sample session is shown below:

```
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=0-524287'
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=524288-1048575'
/full-240p-00004.mp4 timeout=10 headers='Range: bytes=1048576-1572863'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=1572864-2097151'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=2097152-2621439'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=2621440-3145727'
/full-240p-00004.mp4 pace_time=10 timeout=10 headers='Range: bytes=3145728-3221180'
```
The session consists of multiple requests for different chunks of a video. There are the following fields in each request:
- The first field is the name of the requested video. In our example, requests ask for `/full-240p-00004.mp4`.
- `timeout` determines how many seconds the client waits to receive at least one byte of the response from the server after sending the request. The client container increments the client timeout counter if this timer expires and the server does not deliver any byte of the response to the client. The client container reports the client timeout counter at the end of the benchmark's execution as `client_timo`.
- `pace_time` determines how many seconds the client waits to send the next request. By default, the first three requests of each session doesn't have this field, which means they are sent together. From the fourth request to the end, the pace_time is 10 seconds.
- `header` declares a range of bytes of the video that will be streamed as a result of sending a particular request. 

These sessions will be read and processed by the client container. `SESSIONS_COUNT` specifies the number of sessions for each resolution with 5 as the default value. It would be good to give an appropriate value to `SESSIONS_COUNT` to make sure all videos in the dataset would be accessed. Note that the dataset container uses a specific distribution to simulate the behavior that some videos are more popular than others. Check the logs of the dataset container to see statistics regarding the generated sessions for various resolutions.

Note that the sessions in the log files are static and represent a hypothetical behavior of a client that streams a video from the server. We will explain how the client container uses these sessions later. 


### Starting the Server ####
To start the server you have to first `pull` the server image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/media-streaming:server

The following command will start the server, mount the dataset volume, and attach it to the *host* network. The ${NGINX_WORKERS} parameter sets the number of NGINX workers. If it is not given, the default value is 2000. Adjust this number based on the computational resources of the server and the intended load.  

    $ docker run -d --name streaming_server --volumes-from ${DATASET_CONTAINER_ID} --net host cloudsuite/media-streaming:server ${NGINX_WORKERS}


### Starting the Client on Host2 ###

Copy the **`logs`** folder from Host1 to Host2

    $ rsync -zarvh username@HOST1:$HOME/logs $HOME

Note: Pass the public key file, in case of authentication error using `-e "ssh -i /path/to/.pemfile"` to the `rsync` command.


To start the client container you have to first `pull` the client image and then run it. To `pull` the client image use the following command:

    $ docker pull cloudsuite/media-streaming:client

To start the client container and connect it to the *host* network use the following command:

    $ docker run -t --name=streaming_client -v $HOME/logs:/videos/logs -v $HOME/output:/output --net host cloudsuite/media-streaming:client ${SERVER_IP} ${HTTPERF_CLIENTS} ${SESSIONS} ${RATE} ${ENCRYPTION_MODE}

The **`logs`** folder is mounted into the client container using `-v /path/to/logs:/videos/logs`. `SERVER_IP` is the IP of Host1. `HTTPERF_CLIENTS` determines the number of distinct `httperf` processes on the client machine that will generate the load and its default value is 4. For higher loads, it would be beneficial to increase the number of httperf clients to distribute the load among multiple processes. Make sure that `HTTPERF_CLIENTS` is not larger than the number of cores available on the client machine or the number of the cores devoted to the client container.

`SESSIONS` sets the total number of sessions that will be read from the log files to emulate the behavior of the streaming clients. These sessions are first distributed among the httperf processes set by `HTTPERF_CLIENTS`. For example, if `SESSIONS` and `HTTPERF_CLIENTS` are equal to 1000 and 5, respectively, each httperf process will be responsible for handling 200 sessions. Then, these sessions are further distributed among different resolutions by 10%, 30%, 40%, and 20% probabilities for 240p, 360p, 480p, and 720p resolutions, respectively. You might want to change these probabilities based on your need by modifying [this](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/files/run/peak_hunter/launch_remote.sh) script. If the number of sessions that are going to be generated for a specific resolution becomes larger than the number of sessions available in the log file of the corresponding resolution, the client container starts from the beginning of the log to create the sessions. 

In addition, `RATE` specifies the rate at which a new session will be generated. Its unit is sessions per second. For example, if `SESSIONS` is 1000 and `RATE` is 10, it takes 100 seconds until the client container generates all demanded sessions. 

Finally, `ENCRYPTION_MODE` has "PT" and "TLS" as valid values that determine whether the server transfers the videos in the plain text format over port 80, or it uses TLSv1.3 to encrypt the media and send them over port 443. 

At the end of the client's execution, the overall execution statistics will be found under the /output directory of the container, which you can map to a directory on the host using `-v /path/to/output:/output`. 

### Guidelines for Tuning the Benchmark
After running the benchmark, the client container periodically reports three metrics: throughput in Mbps, the total number of errors encountered during the benchmark's execution, and the number of concurrent established sessions to the server that are streaming media. A sample report looks like this:
```
Throughput (Mbps) = 1325.97 , total_errors = 0       , concurrent-sessions = 317
```
Note that each httperf client reports its own statistics. Therefore, the overall state of the benchmark will be the sum of individual reports by each httperf client. To tune the benchmark, start with a starting rate as the seed. Keep in mind that it would take a few minutes for the benchmark to reach a steady state. Consequently, consider giving an appropriate number for `SESSIONS` to the client container. For example, if the benchmark did not reach the steady state in 5 minutes and the given rate was 10 sessions per second, the number of sessions would be larger than 5x60x10=3000. Otherwise, the benchmark won't generate enough sessions and the benchmark enters the ramp-down phase before reaching the steady state.

The benchmark reaches the steady state when both throughput and concurrent-sessions are stabilized, and there are few encountered errors. The number of errors would be 0, but occasional errors may occur. If there is a problem in the tuning process, the number of errors will start increasing rapidly. In the ramp-up phase, both throughput and concurrent-sessions will be increasing. The throughput may stabilize, but concurrent-sessions continue to increase. It means that the rate of establishing new sessions is higher than the machine's capabilities. In this case, consider decreasing the `RATE` parameter of the client container. On the other hand, if you find the benchmark in a steady state, you might want to increase the `RATE` to see whether the machine can handle a larger load.

During the tuning process, make sure that the client container is not overloaded. You can check the client container's CPU utilization using different tools (e.g. docker stats) and compare it against the number of cores on the client machine or the number of cores devoted to the container by docker (e.g. by --cpuset-cpus option). An overloaded client would result in crashes or errors while the server can handle the given load. Note that each httperf process utilizes a single core. Therefore, make sure that the number of available cores to the client container is at least equal to `HTTPERF_CLIENTS`. The client container distributes the given load (declared by `SESSIONS` and `RATE`) equally among different httperf clients.

  [datasetdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/dataset/Dockerfile "Dataset Dockerfile"  

  [serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/server/Dockerfile "Server Dockerfile"

  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/media-streaming "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/media-streaming/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
  [httperf_repo]: https://github.com/httperf/httperf "httperf repo"
