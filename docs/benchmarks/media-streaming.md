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

### Dataset

The streaming server requires a video dataset to serve. We generate a synthetic dataset, comprising several videos of different lengths and qualities. We provide a separate docker image that handles the dataset generation, which is then used to launch a dataset container that exposes a volume containing the video dataset.

To set up the dataset you have to first `pull` the dataset image and then run it. To `pull` the dataset image use the following command:

    $ docker pull cloudsuite3/media-streaming:dataset

The following command will create a dataset container that exposes the video dataset volume, which will be used by the streaming server:

    $ docker create --name streaming_dataset cloudsuite3/media-streaming:dataset


### Creating a network between the server and the client(s)

To facilitate the communication between the client(s) and the server, we build a docker network:

    $ docker network create streaming_network

We will attach the launched containers to this newly created docker network.

### Starting the Server ####
To start the server you have to first `pull` the server image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite3/media-streaming:server

The following command will start the server, mount the dataset volume, and attach it to the *streaming_network* network:

    $ docker run -d --name streaming_server --volumes-from streaming_dataset --net streaming_network cloudsuite3/media-streaming:server


### Starting the Client ####

To start the client you have to first `pull` the client image and then run it. To `pull` the client image use the following command:

    $ docker pull cloudsuite3/media-streaming:client

To start the client container and connect it to the *streaming_network* network use the following command:

    $ docker run -t --name=streaming_client -v /path/to/output:/output --volumes-from streaming_dataset --net streaming_network cloudsuite3/media-streaming:client streaming_server

The client will issue a mix of requests for different videos of various qualities and performs a binary search of experiments to find the peak request rate the client can sustain while keeping the failure rate acceptable. At the end of client's execution, the resulting log files can be found under /output directory of the container, which you can map to a directory on the host using `-v /path/to/output:/output`.

  [datasetdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming/dataset/Dockerfile "Dataset Dockerfile"  

  [serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming/server/Dockerfile "Server Dockerfile"

  [clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming/client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/media-streaming "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite3/media-streaming/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite3/media-streaming.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite3/media-streaming.svg "Go to DockerHub Page"
  [nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
  [httperf_repo]: https://github.com/httperf/httperf "httperf repo"
