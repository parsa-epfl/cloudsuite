# Media Streaming

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This benchmark uses the [Nginx][nginx_repo] web server as a streaming server for hosted videos of various lengths and qualities. The client, based on [httperf's][httperf_repo] wsesslog session generator, generates a request mix for different videos, to stress the server.

## Using the benchmark ##
The benchmark has two tiers: the server and the clients. The server runs Nginx, and the clients send requests to stream videos from the server. Each tier has its own image which is identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`server`][serverdocker]: This represents the Nginx streaming server running as a daemon.
 - [`client`][clientdocker]: This represents the httperf client.
 - [`dataset`][datasetdocker]: This represents the video files dataset for the streaming server.

These images are automatically built using the mentioned Dockerfiles available on the `parsa-epfl/cloudsuite` [GitHub repo][repo].

### Running Dataset and server on Host1

The streaming server requires a video dataset to serve. We generate a synthetic dataset, comprising several videos of different lengths and qualities. We provide a separate docker image that handles the dataset generation, which is then used to launch a dataset container that exposes a volume containing the video dataset.

To set up the dataset you have to first `pull` the dataset image and then run it. To `pull` the dataset image use the following command:

    $ docker pull cloudsuite/media-streaming:dataset

The following command will run the dataset container that exposes the video dataset volume, which will be used by the streaming server:

    $ docker run --name streaming_dataset cloudsuite/media-streaming:dataset

Copy logs folder from container to Host1:

    $ sudo docker cp ${DATASET_CONTAINER_ID}:/videos/logs /path/on/host1
    

### Starting the Server ####
To start the server you have to first `pull` the server image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/media-streaming:server

The following command will start the server, mount the dataset volume, and attach it to the *host* network:

    $ docker run -d --name streaming_server --volumes-from streaming_dataset --net host cloudsuite/media-streaming:server


### Starting the Client on Host2 ###

Copy the **`logs`** folder from Host1 to the following path on Host2

    $ rsync -zarvh username@HOST1:/path/on/host1/logs /path/on/host2/cloudsuite/benchmarks/media-streaming/client/files/

Note: Pass public key file, in case of authentication error using -e "ssh -i /path/to/.pemfile" to the rsync command

The ${SERVER_IP} is the IP of Host1

Pass additional parameter "True" while running client.


To start the client you have to first `pull` the client image and then run it. To `pull` the client image use the following command:

    $ docker pull cloudsuite/media-streaming:client

To start the client container and connect it to the *host* network use the following command:

    $ docker run -t --name=streaming_client -v /path/to/output:/output --net host cloudsuite/media-streaming:client ${SERVER_IP} True

Note: If client docker container exits by giving this error "cp: cannot stat '/root/logs/cl*': No such file or directory."
Then copy the "logs" folder using the rsync command and re-build the client container.


The client will issue a mix of requests for different videos of various qualities and performs a binary search of experiments to find the peak request rate the client can sustain while keeping the failure rate acceptable. At the end of client's execution, the resulting log files can be found under /output directory of the container, which you can map to a directory on the host using `-v /path/to/output:/output`.

  [datasetdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming/dataset/Dockerfile "Dataset Dockerfile"  

  [serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming/server/Dockerfile "Server Dockerfile"

  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming/client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/media-streaming/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/media-streaming.svg "Go to DockerHub Page"
  [nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
  [httperf_repo]: https://github.com/httperf/httperf "httperf repo"
