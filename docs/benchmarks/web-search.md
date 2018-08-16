# Web Search #

[![Pulls on DockerHub][dhpulls]][dhrepo]
[![Stars on DockerHub][dhstars]][dhrepo]

This repository contains the docker image for Cloudsuite's Web Search benchmark.

The Web Search benchmark relies on the [Apache Solr][apachesolr] search engine framework. The benchmark includes a client machine that simulates real-world clients that send requests to the index nodes. The index nodes contain an index of the text and fields found in a set of crawled websites.

## Using the benchmark ##

### Dockerfiles ###

Supported tags and their respective `Dockerfile` links:

- [`server`][serverdocker] This builds an image for the Apache Solr index nodes. You may spawn several nodes.
- [`client`][clientdocker] This builds an image with the client node. The client is used to start the benchmark and query the index nodes.

These images are automatically built using the mentioned Dockerfiles available on [`https://github.com/parsa-epfl/cloudsuite/tree/master/benchmarks/web-search`][repo].

### Creating a network between the server(s) and the client(s)

To facilitate the communication between the client(s) and the server(s), we build a docker network:

	$ docker network create search_network

We will attach the launched containers to this newly created docker network.

### Populating the Index ###

We download the index once and attach it as a volume. To do this, first 'pull' the web-search-index image

	$ docker pull cloudsuite/web-search-index
	
We run the container:

	 $ docker run -it --name index cloudsuite/web-search-index


### Starting the server (Index Node) ###

To start the server you have to first `pull` the server image and then run it. To `pull` the server image, use the following command:

	$ docker pull cloudsuite/web-search:server

The following command will start the server and forward port 8983 to the host, so that the Apache Solr's web interface can be accessed from the web browser using the host's IP address. More information on Apache Solr's web interface can be found [here][solrui]. The first parameter past to the image indicates the memory allocated for the JAVA process. The pregenerated Solr index occupies 13GB of memory, and therefore we use `13g` to avoid disk accesses. The second parameter indicates the number of Solr nodes. Because the index is for a single node only, the aforesaid parameter should be `1` always. We also attach the volume we created as the index earlier.

	$ docker run -it --name server --volumes-from index --net search_network -p 8983:8983 cloudsuite/web-search:server 13g 1

At the end of the server booting process, the container prints the `server_address` of the index node. This address is used in the client container. The `server_address` message in the container should look like this (note that the IP address might change):

	$ Index Node IP Address: 172.19.0.2
	
### Generating own Index ###
It is also possible to generate an index from the wikipedia dumps. To do that, first download the files enwiki-latest-pages-articles-multistream-index.txt.bz2 and enwiki-latest-pages-articles-multistream.xml.bz2 from [`https://dumps.wikimedia.org/enwiki/latest/`] and store them in a folder. We'll call this folder $WIKI_DUMPS

	$ cd $WIKI_DUMPS
	$ wget https://dumps.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles-multistream-index.txt.bz2
	$ wget https://dumps.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles-multistream.xml.bz2 
	$ bzip2 -kd enwiki-latest-pages-articles-multistream-index.txt.bz2
Now to generate the index. The parameter <num_of_wikipedia_pages> specifies how many wikipedia articles are to be used to create the index. As the reference, the 13GB index was created by indexing 5000000 pages. 

	$ docker run -it --name server -v $WIKI_DUMPS:/home/solr/wiki_dump --net search_network -p 8983:8983 cloudsuite/web-search:server 13g 1 generate <num_of_wikiepdia_pages>

### Starting the client and running the benchmark ###

To start a client you have to first `pull` the client image and then run it. To `pull` the client image, use the following command:

	$ docker pull cloudsuite/web-search:client

The following command will start the client node and run the benchmark. The `server_address` refers to the IP address, in brackets (e.g., "172.19.0.2"), of the index node that receives the client requests. The four numbers after the server address refer to: the scale, which indicates the number of concurrent clients (50); the ramp-up time in seconds (90), which refers to the time required to warm up the server; the steady-state time in seconds (60), which indicates the time the benchmark is in the steady state; and the rump-down time in seconds (60), which refers to the time to wait before ending the benchmark. Tune these parameters accordingly to stress your target system.

	$ docker run -it --name client --net search_network cloudsuite/web-search:client server_address 50 90 60 60  

The output results will show on the screen after the benchmark finishes.

### Important remarks ###

- The target response time requires that 99% of the requests are serviced within 200ms.

- The throughput statistic, operations per second, is shown as:

```xml
	<metric unit="ops/sec">25.133</metric>
```

- The response time statistics, average, maximum, minimum, 90-th, and 99-th, are shown as:

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


- The commands to add multiple index nodes are almost identical to the commands executed in the server image. An index has to be copied to Apache Solr's core folder, and then the server is started. The only difference is that the new server nodes have to know the address and the port of the first index node. In our example, it should be `server_address` and `8983`. Note that we also need to use a different port for the servers, for example `9983`.


	$ bin/solr start -cloud -p 9983 -z server_address:8983 -s /usr/src/solr_cores/ -m 12g

More information about Solr can be found [here][solrmanual].

[datadocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/web-search/data/Dockerfile "Data volume Dockerfile"
[serverdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/web-search/server/Dockerfile "Server Dockerfile"
[clientdocker]: https://github.com/parsa-epfl/cloudsuite/blob/master/benchmarks/web-search/client/Dockerfile "Client Dockerfile"
[solrui]: https://cwiki.apache.org/confluence/display/solr/Overview+of+the+Solr+Admin+UI "Apache Solr UI"
[solrmanual]: https://cwiki.apache.org/confluence/display/solr/Apache+Solr+Reference+Guide "Apache Solr Manual"
[nutchtutorial]: https://wiki.apache.org/nutch/NutchTutorial "Nutch Tutorial"
[apachesolr]: https://github.com/apache/solr "Apache Solr"
[apachenutch]: https://github.com/apache/nutch "Apache Nutch"
[repo]: https://github.com/parsa-epfl/cloudsuite/tree/master/benchmarks/web-search "Web Search GitHub Repo"
[dhrepo]: https://hub.docker.com/r/cloudsuite/web-search/ "DockerHub Page"
[dhpulls]: https://img.shields.io/docker/pulls/cloudsuite/web-search.svg "Go to DockerHub Page"
[dhstars]: https://img.shields.io/docker/stars/cloudsuite/web-search.svg "Go to DockerHub Page"
