# Web Search #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This repository contains the docker image for Cloudsuite's Web Search benchmark.

The Web Search benchmark relies on the [Apache Solr][apachesolr] search engine framework. The benchmark includes a client machine that simulates real-world clients that send requests to the index nodes. The index nodes contain an index of the text and fields found in a set of crawled websites.

## Using the benchmark ##

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:
- [`index`][indexdocker] This builds an image that crawls a set of given websites to generate an index.
- [`dataset`][datasetdocker] This downloads an already generated index and mounts it to be used by the server.
- [`server`][serverdocker] This builds an image for the Apache Solr index nodes. You may spawn several nodes.
- [`client`][clientdocker] This builds an image with the client node. The client is used to start the benchmark and query the index nodes.

These images are automatically built using the mentioned Dockerfiles available [`here`][repo].

### Starting the dataset ###

The following command downloads and mounts the dataset index:

 ```  $ docker run --name web_search_dataset cloudsuite/web-search:dataset ```

### Starting the server (Index Node) ###

To start the server you have to first `pull` the server image and then run it. To `pull` the server image, use the following command (to be done on the same host as dataset):

 ``` $ docker pull cloudsuite/web-search:server ```

The following command will start the server on port 8983 on the host, so that the Apache Solr's web interface can be accessed from the web browser using the host's IP address. More information on Apache Solr's web interface can be found [here][solrui]. The first parameter past to the image indicates the memory allocated for the JAVA process. The pregenerated Solr index occupies 12GB of memory, and therefore we use `12g` to avoid disk accesses. The second parameter indicates the number of Solr nodes. Because the index is for a single node only, the aforesaid parameter should be `1` always.

```  $ docker run -it --name server --volumes-from web_search_dataset --net host cloudsuite/web-search:server 12g 1 ```

At the beginning of the server booting process, the container prints the `server_address` of the index node. This address will be used in the client container to send the requests to the index node. The `server_address` message in the container should look like this (note that the IP address might change):

```  $ Index Node IP Address: 192.168.1.47 ```

The server's boot process might take some time. To see whether the index node is up and responsive, you might want to send a simple query using command `query.sh` provided [here](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-serving/web_server/files/query.sh). If the server is up, you will see the following result.
```
200
{
  "responseHeader":{
    "zkConnected":true,
    "status":0,
    "QTime":0,
    "params":{
      "q":"websearch",
      "df":"text",
      "fl":"url",
      "lang":"en",
      "rows":"10"}},
  "response":{"numFound":1,"start":0,"numFoundExact":true,"docs":[
      {
        "url":"https://en.wikipedia.org/wiki/Web_scraping"}]
  }}
```

Note that the Java engine is configured to benefit from huge pages. If Solr cannot find available huge pages, it gives you warnings like this:
``` OpenJDK 64-Bit Server VM warning: Failed to reserve shared memory. (error = 12) ```
While the benchmark works fine with this warning, to fix it and benefit from huge page advantages, you can follow the instructions given on this [link](https://www.oracle.com/java/technologies/javase/largememory-pages.html). 

### Starting the client and running the benchmark ###

To start a client you have to first `pull` the client image and then run it. To `pull` the client image, use the following command:

```  $ docker pull cloudsuite/web-search:client ```

To run the benchmark, start the client container by running the command below:

```  $ docker run -it --name web_search_client --net host cloudsuite/web-search:client <server_address> <scale>  <The ramp up time in secod> <the ramp down time in second> <steady-state time in second> ```

For example, consider the command below:

```  $ docker run -it --name web_search_client --net host cloudsuite/web-search:client 172.19.0.2 50 90 60 60  ```

The `server_address` refers to the IP address of the index node that receives the client requests ("172.19.0.2" in our example). The four numbers after the server address refer to: the scale, which indicates the number of concurrent clients (50) sending search requests to the index server; the ramp-up time in seconds (90), which refers to the time required to warm up the server; the ramp-down time in seconds (60), which refers to the time to wait before ending the benchmark; and the steady-state time in seconds (60), which indicates the time the benchmark is in the steady state. Note that all statistics reported at the end of the benchmark's execution are measured during the steade-state period. Tune these parameters accordingly to stress your target system.

The client container will show the output results on the screen after the benchmark finishes.

### Generating a custom index
You can use the index image to generate your own customized index. To start generating an index, first `pull` the index image by running the following command:

```  $ docker pull cloudsuite/web-search:index ```
  
Then, create a list of websites that you want to crawl in a file named `seed.txt`. Write each url in a different line. Then, run the index container using the command below:

```  $ docker run -dt --name web_search_index -v ${PATH_TO_SEED.TXT}:/usr/src/apache-nutch-1.18/urls/. cloudsuite/web-search:index ```

This command will run Nutch and Solr on the container and override the given set of urls for crawling with the original one. 

To start the indexing process, run the command below:

```  $ docker exec -it web_search_index generate_index ```
   
This command crawls up to 100 web-pages, starting from the seed urls and generates an index for the crawled pages. Finally, it reports the total size of the generated index. You can contineously run this command until the number of crawled pages or the size of the index reaches your desired value. The index is located at `/usr/src/solr-9.0.0/nutch/data` in the index container. You can copy the index from the index container to the host machine by running the following command:

```  $ docker cp web_search_index:/usr/src/solr-9.0.0/nutch/data ${PATH_TO_SAVE_INDEX} ```
  
Accordingly, you can modify the server image to use your index instead of the index given by the dataset container. 

### Important remarks ###

- The target response time requires that 99% of the requests are serviced within 200ms.

- The throughput statistic, operations per second, is shown as:

```xml
  <metric unit="ops/sec">25.133</metric>
```

- The response time statistics, average, maximum, standard deviation, 90-th, and 99-th percentiles, are shown as:

```xml
  <responseTimes unit="seconds">
      <operation name="GET" r90th="0.500">
        <avg>0.034</avg>
        <max>0.285</max>
        <sd>0.035</sd>
        <p90th>0.080</p90th>
        <passed>true</passed>
        <p99th>0.143</p99th>
      </operation>
  </responseTimes>
```

### Additional Information ###

- This repository contains a 12GB index for a single node. The index was generated by crawling a set of websites with [Apache Nutch][apachenutch]. It's possible to generated indexes for Apache Solr that are both larger and for multiple index nodes. More information on how to generate indexes can be found [here][nutchtutorial].

- The commands to add multiple index nodes are almost identical to the commands executed in the server image. An index has to be copied to Apache Solr's core folder, and then the server is started. The only difference is that the new server nodes have to know the address and the port of the first index node. In our example, it should be `server_address` and `8983`. Note that we also need to use a different port for the servers, for example `9983`.


```  $ bin/solr start -cloud -p 9983 -z server_address:8983 -s /usr/src/solr_cores/ -m 12g ```

More information about Solr can be found [here][solrmanual].

[indexdocker]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/web-search/index "Index Generator Dockerfile"
[datasetdocker]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/web-search/dataset "Dataset volume Dockerfile"
[serverdocker]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/web-search/server "Server Dockerfile"
[clientdocker]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/web-search/client "Client Dockerfile"
[solrui]: https://solr.apache.org/guide/solr/latest/getting-started/solr-admin-ui.html "Apache Solr UI"
[solrmanual]: https://solr.apache.org/guide/solr/latest/ "Apache Solr Manual"
[nutchtutorial]: https://cwiki.apache.org/confluence/display/NUTCH/NutchTutorial "Nutch Tutorial"
[apachesolr]: https://github.com/apache/solr "Apache Solr"
[apachenutch]: https://github.com/apache/nutch "Apache Nutch"
[repo]: https://github.com/parsa-epfl/cloudsuite/tree/main/benchmarks/web-search "Web Search GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/web-search/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/web-search.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/web-search.svg "Go to DockerHub Page"
