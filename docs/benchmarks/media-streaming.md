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

    $ docker run --name streaming_dataset cloudsuite/media-streaming:dataset

Copy logs folder from container to Host1:

    $ docker cp ${DATASET_CONTAINER_ID}:/videos/logs $HOME
    

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

The **`logs`** folder is mounted into the client container using `-v /path/to/logs:/videos/logs`. SERVER_IP is the IP of Host1. HTTPERF_CLIENTS determines the number of distinct `httperf` processes on the client machine that will generate the load. For higher loads, it would be beneficial to increase the number of httperf clients to distribute the load among multiple processes. The default value is 4. SESSIONS sets the number of individual sessions that the client would request from the server to stream. Each session is assigned to a movie with specific quality and the client sends HTTP requests for the chunks of the video until the movie is fully streamed. The **`logs`** folder describes the sessions used in the benchmark. Each session is separated from the others by blank lines. Finally, RATE specifies the rate at which a new session will be generated. Its unit is sessions per second. For example, if SESSIONS is 1000 and RATE is 10, it takes 100 seconds until the client generates all demanded sessions. ENCRYPTION_MODE has "PT" and "TLS" as valid values that determine whether the server transfers the videos in the plain text format over port 80, or it uses TLSv1.2 to encrypt the media and send them over port 443. 

During the execution of the benchmark, the client container continuously reports some statistics. Moreover, at the end of the client's execution, the overall execution statistics will be found under the /output directory of the container, which you can map to a directory on the host using `-v /path/to/output:/output`.

  [datasetdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/dataset/Dockerfile "Dataset Dockerfile"  

  [serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/server/Dockerfile "Server Dockerfile"

  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming/client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/media-streaming "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/media-streaming/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
  [httperf_repo]: https://github.com/httperf/httperf "httperf repo"
