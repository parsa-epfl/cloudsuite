# Web Serving

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

Web Serving is a primary service in the cloud. Traditional web services with dynamic and static content are moved into the cloud to provide fault tolerance and dynamic scalability by bringing up the required number of servers behind a load balancer. Although cloud services use many variants of the traditional web stack (e.g., substituting Apache with other web server software or using other language interpreters instead of PHP), the underlying service architecture remains unchanged. A stateless web server process accepts independent client requests. In response, the web server either directly serves static files from disk or passes the request to a stateless middleware script, written in a high-level interpreted or byte-code compiled language, which is then responsible for producing dynamic content. The middleware stores all the state information in backend databases such as cloud NoSQL data stores or traditional relational SQL servers supported by key-value cache servers to achieve high throughput and low latency. This benchmark includes a social networking engine (Elgg), and a client implemented using the Faban workload generator. This benchmark version is compatible with Php 8.1, which supports JIT execution and thus improves the workload's throughput.

## Using the benchmark ##
The benchmark has four tiers: web server, database server, Memcached server, and clients. The web server runs Elgg and connects to the Memcached and database servers. The clients request to log in to the social network and perform various operations, such as sending and reading messages, adding or removing friends, posting blogs, and adding comments or likes to different posts. Each tier has its own image, identified by its tag.

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

 - [`web_server`][webserverdocker] contains the application code and the web frontend PHP engine.
 - [`memcached_server`][memcacheserverdocker] contains the Memcached server as the caching layer.
 - [`db_server`][databaseserverdocker] contains the MariaDB database.
 - [`faban_client`][clientdocker] contains the load generator based on Faban.

These images are automatically built using the mentioned Dockerfiles available on the `parsa-epfl/cloudsuite` [GitHub repo][repo].

### IP Addresses ###
Please note that all IP addresses should refer to the explicit IP address of the host server running each container.

### Starting the database server ####
The following command will start the database server:

```bash
$ docker run -it --net=host --name=database_server cloudsuite/web-serving:db_server
```

The database is pre-populated with around 100 K users and their data, such as their friends list and sent messages. The initial size of the database is around 2.5 GB. Based on your need, you can increase the database size by running the benchmark for some time and inspecting whether the database size has reached your desired number.

### Starting the Memcached server ####
The following command will start the Memcached server:

```bash
$ docker run -dt --net=host --name=memcache_server cloudsuite/web-serving:memcached_server
```

By default, the Memcached server has 4 threads and 64GB buffer size.

### Starting the web server ####

To run the web server, use the following command:

```bash
$ docker run -dt --net=host --name=web_server cloudsuite/web-serving:web_server /etc/bootstrap.sh ${PROTOCOL} ${WEB_SERVER_IP} ${DATABASE_SERVER_IP} ${MEMCACHED_SERVER_IP} ${MAX_PM_CHILDREN} ${WORKER_PROCESS}
```

The `PROTOCOL` parameter can either be `http` or `https` and determines the web server's protocol. The `WEB_SERVER_IP`, `DATABASE_SERVER_IP`, and `MEMCACHED_SERVER_IP` parameters refer to the explicit IP of the host server running the corresponding container. The `MAX_PM_CHILDREN` parameter sets the `pm.max_children` in the php-fpm setting, which controls the number of threads in PHP's thread pool. The default value is 4, and we recommend setting 4 per core to avoid frequent thread switching while still hiding I/O latency. The `WORKER_PROCESS` parameter specifies the number of Nginx worker processes. The default is `auto`. We recommend having 1 Nginx worker process per 32 cores. 

To check whether the web server is up, you can access Elgg's home page through a web browser at `http://<web_server's IP>:8080` or `https://<web_server's IP>:8443` URLs for HTTP and HTTPS web servers, respectively. For example, Elgg's home page is shown in the figure below:

<img width="1438" alt="image" src="https://user-images.githubusercontent.com/72558613/189359017-7e694ec3-9bf4-4429-86bc-9a96578de37a.png">

You may see different content based on the latest recorded activities in the database. You can log in as the service administrator using `admin` as the username and `adminadmin` as the password in the `log in` menu. Then, you will have access to the administration dashboard, where you can modify different settings based on your need. 

You can find the list of usernames and passwords of regular users in [this](https://github.com/parsa-epfl/cloudsuite/blob/update_web_serving_bigDB/benchmarks/web-serving/faban_client/files/users.list) file. You can log in as a normal user and see various services and features available for an Elgg user. It will help you better understand how the benchmark's client container works.

###  Running the benchmark ###

To start the client container which runs the benchmark, use the following commands:

```bash
$ docker run --net=host --name=faban_client cloudsuite/web-serving:faban_client ${WEB_SERVER_IP} ${LOAD_SCALE}
```

The command has a mandatory parameter `WEB_SERVER_IP` to set the IP of the web server and an optional parameter `LOAD_SCALE` to specify the load scale (default is 1). The `LOAD_SCALE` parameter controls the number of users that log in to the web server and request social networking pages. You can scale it up and down as much as you would like, considering that scaling the number of users may stress the system. To tune the benchmark, we recommend testing your machines for the maximum possible request throughput while maintaining your target Quality of Service (QoS) metric (we use 99 percentile latency). CPU utilization is less important than the latency and responsiveness for these benchmarks.

The client container offers multiple options to control how the benchmark runs. The list of available options is as follows:

- `--oper=<usergen | run | usergen&run>`: This option has three possible values: `usergen`, `run`, and `usergen&run`. In `usergen`, the benchmark only creates new users and adds them to the database. `LOAD_SCALE` determines the number of generated users. On the other hand, `run` does not generate new users but starts the benchmark by logging in the users to interact with the server by sending various requests. Note that the pre-populated database container holds ~100K users. Therefore, you can start running the benchmark without needing to generate new users. Finally, `usergen&run` does what previous operations do together. The default value is `usergen&run`.
- `--ramp-up=<# of seconds>`: the number of seconds the benchmark spends in the ramp-up phase. Remember that there is a one-second interval between the users' log in requests. Therefore, please set the ramp-up time to a value larger than `LOAD_SCALE` to ensure the number of active users in the steady state matches your given number. The default value is 10.
- `--ramp-down=<# of seconds>`: the number of seconds the benchmark spends in the ramp-down phase. The default value is 10. 
- `--steady=<# of seconds>`: the number of seconds the benchmark spends in the steady-state phase where all users are logged in and interacting with the server. The default value is 30.
- `--min=<# of seconds>`: the minimum number of milliseconds between consecutive requests of a specific user. The default value is 1000.
- `--max=<# of seconds>`: the maximum number of milliseconds between consecutive requests of a specific user. The default value is 1500.
- `--type=<THINKTIME | CYCLETIME>`: This option has two possible values: `THINKTIME` and `CYCLETIME`. For the former value, the latency before sending a new request (determined by `--min` and `--max`) is measured relative to when the last request finishes. On the other hand, the `CYCLETIME` policy calculates the latency from the moment a request is sent to the server. The default value is `THINKTIME`.
- `--dist=<fixed | uniform | negexp>`: This option controls the distribution of values chosen for the inter-request latencies between `--min` and `--max`. You can give three possible values to this option: `fixed`, `uniform`, and `negexp` (that refers to negative exponential distribution). The default value is `fixed`.
- `--encryption=<1 | 0>`: The client container can send the request to a TLS-encrypted server by setting this option to 1. The default value is 0, which means the client sends HTTP requests. This value should be adjusted according to the `PROTOCOL` parameter you set when setting up the server. 

The client container will output the summary of the benchmark results in XML format after the benchmark finishes. You can also access the summary and logs of the run by mounting the `/faban/output` directory of the container in the host filesystem (e.g., `-v /host/path:/faban/output`).

### Possible User Operations

Elgg provides different features for the users. The client image implements a subset of the available features. The main driver of the client image is in [this](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/faban_client/files/web20_benchmark/src/workload/driver/Web20Driver.java.in) file. You can enrich the list of requests that the client driver supports by modifying the mentioned file. The current version supports the following requests:
- Browse the home page
- Browse recent activities in the site
- Browse a user's profile
- Log in as a user
- Register a new user
- Log out a user
- Add a friend
- Remove a friend
- Browse the friends list
- Check notifications
- Post a wire (A wire is similar to a tweet or a status update)
- Browse the wires page 
- Reply to a wire
- Like a wire
- Browse the inbox
- Send a message
- Read a message
- Delete a message
- Browse sent messages 
- Browse the blogs page
- Post a blog
- Like a blog post
- Comment on a blog
- Search for a site member

[webserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/web_server/Dockerfile "WebServer Dockerfile"
[memcacheserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/memcached_server "MemcacheServer Dockerfile"
[databaseserverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/db_server/Dockerfile "DatabaseServer(MariaDB) Dockerfile"
[clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/faban_client/Dockerfile "Client Dockerfile"

[repo]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/web-serving "GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/web-serving/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/web-serving.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/web-serving.svg "Go to DockerHub Page"
