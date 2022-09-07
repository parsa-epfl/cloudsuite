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
    
`DATASET_SIZE`, in GBs, scales the size of the dataset to the given number. If you don't specify `DATASET_SIZE`, by default, the dataset container generates a collection of 10 videos for each of 240p, 360p, 480p, and 720p resolutions, having around 3.5 GB size altogether. For each of the resolutions, the dataset container creates a log file containing the sessions. Each session represents the behavior of a specific client streaming a specific media by mentioning the name of the requested media and the bytes streamed in each connection. These sessions will be read and processed by the client container. `SESSIONS_COUNT` specifies the number of sessions for each resolution with 5 as the default value. It would be good to give an appropriate value to `SESSIONS_COUNT` to make sure all videos in the dataset would be accessed. Note that the dataset container uses a specific distribution to simulate the behvaior that some videos are more popular than the others. Check the logs of the dataset container to see statistics regarding the generated sessions for various resolutions.

### Starting the Server ####
To start the server you have to first `pull` the server image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/media-streaming:server

The following command will start the server, mount the dataset volume, and attach it to the *host* network. The ${NGINX_WORKERS} parameter sets the number of NGINX workers. If it is not given, the default value is 2000. Adjust this number based on the computational resources of the server and the intended load.  

    $ docker run -d --name streaming_server --volumes-from ${DATASET_CONTAINER_ID} --net host cloudsuite/media-streaming:server ${NGINX_WORKERS}


### Starting the Client on Host2 ###

Copy the **`logs`** folder from Host1 to Host2

    $ rsync -zarvh username@HOST1:$HOME/logs $HOME

Note: Pass the public key file, in case of authentication error using `-e "ssh -i /path/to/.pemfile"` to the `rsync` command.


To start the client you have to first `pull` the client image and then run it. To `pull` the client image use the following command:

    $ docker pull cloudsuite/media-streaming:client

To start the client container and connect it to the *host* network use the following command:

    $ docker run -t --name=streaming_client -v $HOME/logs:/videos/logs -v $HOME/output:/output --net host cloudsuite/media-streaming:client ${SERVER_IP} ${HTTPERF_CLIENTS} ${SESSIONS} ${RATE} ${ENCRYPTION_MODE}

The **`logs`** folder is mounted into the client container using `-v /path/to/logs:/videos/logs`. `SERVER_IP` is the IP of Host1. `HTTPERF_CLIENTS` determines the number of distinct `httperf` processes on the client machine that will generate the load. For higher loads, it would be beneficial to increase the number of httperf clients to distribute the load among multiple processes. The default value is 4. `SESSIONS` sets the number of individual sessions that the client would request from the server to stream. Each session is assigned to a video with specific quality and the client sends HTTP requests for the chunks of the video until it is fully streamed. The **`logs`** folder describes the sessions used in the benchmark. Each session is separated from the others by blank lines. In addition, `RATE` specifies the rate at which a new session will be generated. Its unit is sessions per second. For example, if `SESSIONS` is 1000 and `RATE` is 10, it takes 100 seconds until the client generates all demanded sessions. Finally, `ENCRYPTION_MODE` has "PT" and "TLS" as valid values that determine whether the server transfers the videos in the plain text format over port 80, or it uses TLSv1.3 to encrypt the media and send them over port 443. 

At the end of the client's execution, the overall execution statistics will be found under the /output directory of the container, which you can map to a directory on the host using `-v /path/to/output:/output`. 

### Guidelines for Tuning the Benchmark
After running the benchmark, the client container periodically reports three metrics: throughput in Mbps, the total number of errors encountered during the benchmark's execution, and the number of concurrent established sessions to the server that are streaming media. A sample report looks like this:
```
Throughput (Mbps) = 1325.97 , total_errors = 0       , concurrent-sessions = 317
```
Note that each httperf client reports its own statistics. Therefore, the overall state of the benchmark will be the sum of individual reports by each httperf client. To tune the benchmark, start with a starting rate as the seed. It would take a few minutes for the benchmark to reach a steady state. Consequently, consider giving an appropriate number for `SESSIONS` to the client container. For example, if the benchmark did not reach the steady state in 5 minutes and the given rate was 10 sessions per second, the number of sessions would be larger than 5x60x10=3000. Otherwise, the benchmark won't generate enough sessions and the benchmark enters the ramp-down phase before reaching the steady state.

The benchmark reaches the steady state when both throughput and concurrent-sessions are stabilized, and there are few encountered errors. The number of errors would be 0, but occasional errors may occur. If there is a problem in the tuning process, the number of errors will start increasing rapidly. In the ramp-up phase, both throughput and concurrent-sessions will be increasing. The throughput may stabilize, but concurrent-sessions continue to increase. It means that the rate of establishing new sessions is higher than the machine's capabilities. In this case, consider decreasing the `RATE` parameter of the client container. On the other hand, if you find the benchmark in a steady state, you might want to increase the `RATE` to see whether the machine can handle a larger load.

During the tuning process, make sure that the client container is not overloaded. You can check the client container's CPU utilization using different tools (e.g. docker stats) and compare it against the number of cores on the client machine or the number of cores devoted to the container by docker (e.g. by --cpuset-cpus option). An overloaded client would result in crashes or errors while the server can handle the given load. Note that each httperf client utilizes a single core. Therefore, make sure that the number of available cores to the client container is at least larger than `HTTPERF_CLIENTS`. The client container distributes the given load (declared by `SESSIONS` and `RATE`) equally among different httperf clients.

  [datasetdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/dataset/Dockerfile "Dataset Dockerfile"  

  [serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/server/Dockerfile "Server Dockerfile"

  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/media-streaming "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/media-streaming/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
  [httperf_repo]: https://github.com/httperf/httperf "httperf repo"
