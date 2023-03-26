# Web Search

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

The Web Search benchmark relies on the [Apache Solr][apachesolr] search engine framework. The benchmark includes a client machine that simulates real-world clients that send requests to index nodes. Index nodes contain an index of the text and fields found in crawled websites.

## Using the benchmark ##

### Dockerfiles ###

Supported tags and their respective `Dockerfile` tags:
- [`index`][indexdocker] builds an image that crawls a set of websites to generate an index.
- [`dataset`][datasetdocker] downloads an pre-generated index and mounts it to be used by the server.
- [`server`][serverdocker] contains the Apache Solr index nodes. You may spawn several nodes.
- [`client`][clientdocker] contains the client node based on Faban. The client is used to start the benchmark and query index nodes.

These images are automatically built using the mentioned Dockerfiles available [`here`][repo].

### Starting the dataset ###

The following command downloads and mounts the dataset index:

 ```bash
 $ docker run --name web_search_dataset cloudsuite/web-search:dataset
 ```

It then downloads the dataset from our website.

### Starting the server (Index Node) ###

The following command will start the server on port 8983 on the host so that Apache Solr's web interface can be accessed from the web browser using the host's IP address. More information on Apache Solr's web interface can be found [here][solrui]. The first parameter passed to the image indicates the heap memory allocated for JAVA processes. The pre-generated Solr index occupies 14GB of memory; therefore, we use `14g` to avoid disk access. The second parameter indicates the number of Solr nodes. Because the index is for a single node only, this parameter should be `1` always.

```bash
$ docker run -it --name server --volumes-from web_search_dataset --net host cloudsuite/web-search:server 14g 1
```

At the beginning of the server booting process, the container prints the `server_address` of the index node. This address is used by the client container to send requests to the index node. The `server_address` message in the container should look like this (note that the IP address might change):

```
Index Node IP Address: 192.168.1.47
```

The server's boot process might take some time. To see whether the index node is up and responsive, you can send a simple query using script `query.sh` provided [here](https://github.com/parsa-epfl/cloudsuite/blob/main/benchmarks/web-search/server/files/query.sh). If the server is up, you will see the following result.
```bash
$ ./query.sh `server_address`
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

```
OpenJDK 64-Bit Server VM warning: Failed to reserve shared memory. (error = 12)
```

While the benchmark works fine with this warning, to fix it and benefit from huge page advantages, you can follow the instructions given on this [link](https://www.oracle.com/java/technologies/javase/largememory-pages.html). 

### Starting the client and running the benchmark ###

To load the server, start the client container by running the command below:

```bash
$ docker run -it --name web_search_client --net host cloudsuite/web-search:client <server_address> <scale>
```

`server_address` is the IP address of the Solr index server, and `scale` defines the number of load generators' workers. Additionally, you can customize the load generator and request distribution by applying the following options:

- `--ramp-up=<integer>`: The ramp-up time, during which the load generator sends requests to warm up the server before the actual measurement starts. The unit is seconds, and its default value is 20.
- `--ramp-down=<integer>`: The ramp-down time. Like the ramp-up time, the ramp-down time defines the duration after measurement when the load generator continues. Ramp-down time can avoid a sudden load drop to the server and mimic a real-world scenario. Loads generated in this period are not included in the final statistics. The unit is seconds, and its default value is 10.
- `--steady=<integer>`: The measurement time. The unit is seconds, and its default value is 60.
- `--interval-type=<ThinkTime|CycleTime>`: The method used to define the inter-request interval for each load generator. `ThinkTime` defines the interval as the duration between receiving a reply and sending the next request, while `CycleTime` defines the interval as the duration between sending consecutive requests. The default value is `ThinkTime`. Note that the load generator cannot follow the given interval if its value is smaller than a single request's latency: the load generator is closed-loop. 
- `--interval-distribution=<Fixed|Uniform|NegExp>`: The distribution of the intervals. Its default value is `Fixed`.
- `--interval-min=int`: The minimum interval between sending consecutive requests. The unit is milliseconds, and its default value is 1000. 
- `--interval-max=int`: The maximum interval between sending consecutive requests. The unit is in milliseconds, and its default value is 1500. When using the `Fixed` distribution, this value should be identical to the minimum interval.
- `--interval-deviation=float`: The deviation of intervals. The unit is a percentage, and its default value is 0.

### Generating a custom index
You can use the index image to generate your customized index. To start generating an index, create a list of websites that you want to crawl in a file named `seed.txt`. Write each URL in a different line. Then, run the index container using the command below:

```bash
$ docker run -dt --name web_search_index -v ${PATH_TO_SEED.TXT}:/usr/src/apache-nutch-1.18/urls/seed.txt cloudsuite/web-search:index 
```

This command will run Nutch and Solr on the container and override the given set of URLs for crawling with the original one. 

To start the indexing process, run the command below:

```bash
$ docker exec -it web_search_index generate_index
```
   
This command crawls up to 100 web pages, starting from the seed URLs, and generates an index for the crawled pages. Finally, it reports the total number of indexed documents. You can continuously run this command until the number of crawled pages or the index size reaches your desired value. The index is in the index container at `/usr/src/solr-9.1.1/nutch/data`. You can copy the index from the index container to the host machine by running the following command:

```bash
$ docker cp web_search_index:/usr/src/solr-9.1.1/nutch/data ${PATH_TO_SAVE_INDEX}
```
  
Accordingly, you can modify the server image to use your index instead of the index given by the dataset container. 

### Important remarks ###

- The target response time requires that 99% of the requests be serviced within 200ms.

- The throughput statistic, operations per second, is shown as:

```xml
  <metric unit="ops/sec">25.133</metric>
```

- The response time statistics: average, maximum, standard deviation, 90, and 99 percentiles, are shown as:

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

- This repository contains a 14GB index for a single node. The index was generated by crawling a set of websites with [Apache Nutch][apachenutch]. It is possible to generate indexes for Apache Solr that are both larger and for multiple index nodes. More information on how to generate indexes can be found [here][nutchtutorial].

- The commands to add multiple index nodes are almost identical to the commands executed for the server image. An index has to be copied to Apache Solr's core folder, and then the server can be started. The only difference is that the new server nodes have to know the address and the port of the first index node. In our example, it should be `server_address` and `8983`. You have to run the container with `bash` as the entrypoint, modify the last line in `docker-entrypoint.sh` manually by adding `-z` options to specify the first index, and run it. For instance:

```bash
$ $SOLR_HOME/bin/solr start -force -cloud -f -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE -z server_address:8983
```

if two index servers are on the same machine, you may also need to give a different listening port to the second index server.

- The client container uses a list of prepared terms to generate queries. You can find the list of the terms that are indexed in the Solr index, along with their frequency of appearance in different URLs by running the following query:

```
http://${SERVER_ADDRESS}:8983/solr/cloudsuite_web_search/terms?terms.fl=text&wt=xml&terms=true&terms.limit=10000
```

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
