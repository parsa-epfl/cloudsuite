# MediaStreaming

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

These images are automatically built using the mentioned Dockerfiles available on the `CloudSuite-EPFL/MediaStreaming` [GitHub repo][repo].

### Dataset

The streaming server requires a video dataset to serve. We generate a synthetic dataset, comprising several videos of different lengths and qualities. We provide a separate docker image that handles the dataset generation, which is then used to launch a dataset container that exposes a volume containing the video dataset.

To set up the dataset you have to first `pull` the dataset image and then run it. To `pull` the dataset image use the following command:

    $ docker pull cloudsuite/mediastreaming:dataset

The following command will create a dataset container that exposes the video dataset volume, which will be used by the streaming server:

    $ docker run -d --name streaming_dataset cloudsuite/mediastreaming:dataset


### Creating a network between the server and the client(s)

To facilitate the communication between the client(s) and the server, we build a docker network:

    $ docker network create streaming_network

We will attach the launched containers to this newly created docker network.

### Starting the Server ####
To start the server you have to first `pull` the server image and then run it. To `pull` the server image use the following command:

    $ docker pull cloudsuite/mediastreaming:server

The following command will start the server, mount the dataset volume, and attach it to the *streaming_network* network:

    $ docker run -it --name=streaming_server --volumes-from streaming_dataset --net streaming_network cloudsuite/mediastreaming:server


### Starting the Client ####

To start the client you have to first `pull` the client image and then run it. To `pull` the client image use the following command:

    $ docker pull cloudsuite/mediastreaming:client

To start the client container and connect it to the *streaming_network* network use the following command:

    $ docker run -it --name=streaming_client --volumes-from streaming_dataset --net streaming_network cloudsuite/mediastreaming:client

To start the client, navigate to the /videoperf/run directory in the client container and launch the *benchmark.sh* script. This script is configured to launch a client process that issues a mix of requests for different videos of various qualities and performs a binary search of experiments to find the peak request rate the client can sustain while keeping the failure rate acceptable. At the end of the script's execution, the client's log files can be found under the /videoperf/run/output directory.

  [datasetdocker]: https://github.com/CloudSuite-EPFL/MediaStreaming/blob/master/dataset/Dockerfile "Dataset Dockerfile"  

  [serverdocker]: https://github.com/CloudSuite-EPFL/MediaStreaming/blob/master/server/Dockerfile "Server Dockerfile"

  [clientdocker]: https://github.com/CloudSuite-EPFL/MediaStreaming/blob/master/client/Dockerfile "Client Dockerfile"

  [repo]: https://github.com/CloudSuite-EPFL/MediaStreaming "GitHub Repo"
  [dhrepo]: https://hub.docker.com/r/cloudsuite/mediastreaming/ "DockerHub Page"
  [dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/mediastreaming.svg "Go to DockerHub Page"
  [dhstars]: https://img.shields.io/docker/stars/cloudsuite/mediastreaming.svg "Go to DockerHub Page"
  [nginx_repo]: https://github.com/nginx/nginx "Nginx repo"
  [httperf_repo]: https://github.com/httperf/httperf "httperf repo"
