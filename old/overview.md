---
layout: page
title: Overview
---

<div id="content_outline">
<div id="content-projects"> 


<h1>Benchmark Selection</h1>

                        <!-- start include/mainData/dataPicture.jsp -->

<p>
To find a set of applications that dominate today's data centers, we examined a selection of internet services based on their popularity. For each popular service, we analyzed the class of application software used by the major service providers. 
</p>
                <!-- end include/mainData/dataPicture.jsp -->


<br>
<h1>CloudSuite Benchmarks

</h1>

                        <!-- start include/mainData/dataPicture.jsp -->



<a href="./analytics.html">Data Analytics</a></p><p>

The explosion of accessible human-generated information necessitates automated analytical processing to cluster, classify, and filter this information. The MapReduce paradigm has emerged as a popular approach to handling large-scale analysis, farming out requests to a cluster of nodes that first perform filtering and transformation of the data (map) and then aggregate the results (reduce). The Data Analytics benchmark is included in CloudSuite to cover the increasing importance of machine learning tasks analyzing large amounts of data in datacenters using the MapReduce framework. It is composed of Mahout, a set of machine learning libraries, running on top of Hadoop, an open-source implementation of MapReduce. 
</p>

<a href="./memcached.html"> Data Caching </a>
<p>
Today’s web applications are highly latency-sensitive, having strict quality of service requirements, QoS. As the applications are data-intensive and spend most of their execution time in memory, it is of utmost importance that data is available within hundreds of microseconds. Hard disks, traditionally used as the main storage in servers, are too slow to meet the QoS requirements of modern applications. That’s the reason why most of today’s server systems dedicate separate caching servers that cache the data in their DRAM. Our data caching benchmark relies on the most widely used data-caching platform, memcached, and simulates a Twitter caching server using a real Twitter dataset. This benchmark is developed in collaboration with the Advanced Computer Architecture Lab (ACAL) at the University of Michigan. 
</p>


<a href="./dataserving.html">Data Serving</a><p>

Various NoSQL datastores have been explicitly designed to serve as the backing store for large-scale web applications such as the Facebook inbox, Google Earth and Google Finance, providing fast and scalable storage with varying and rapidly evolving storage schema. The NoSQL systems split hundreds of terabytes of data into shards and horizontally scale to large cluster sizes, typically using indexes that support fast lookups and key range scans to retrieve the set of requested objects. For simplicity and scalability, these systems are designed to support queries that can be completely executed by a single storage node, with any operations that require combining data from multiple shards relegated to the middleware. To cover this category of applications, CloudSuite uses Cassandra, an open-source NoSQL datastore, stimulated by the Yahoo! Cloud Serving Benchmark.

</p>

<a href="./graph.html"> Graph Analytics </a>
<p>
Unlike the Data Analytics benchmark, which operates on textual data, Graph Analytics performs data analysis on large-scale graphs, using a distributed graph-processing system. Graph Analytics becomes increasingly important with the emergence of social networks such as Facebook and Twitter. Our Graph Analytics benchmark uses the GraphLab machine learning and data mining software to run the TunkRank algorithm, which recursively computes the influence of Twitter users based on the number of their followers. 
</p>


<a href="./streaming.html">Media Streaming</a><p>

Media Streaming is one of the popular applications in cloud datacenters. The availability of high-bandwidth connections to home and mobile devices has made media streaming services such as NetFlix, YouTube, and YuKu ubiquitous. Streaming services use large server clusters to gradually packetize and transmit media files ranging from megabytes to gigabytes in size, pre-encoded in various formats and bit-rates to suit a wide client base. CloudSuite introduces Media Streaming, using Darwin Streaming Server stressed by a client simulating real world scenarios.

</p>


<a href="./cloud9.html">Software Testing</a><p>

The ability to temporarily allocate compute resources in the cloud without purchasing the infrastructure is what makes clouds a rich environment for innovative applications. Software Testing as a Service is one such innovations that uses the cloud infrastructure to run software testing using symbolic execution. Unlike the traditional super-computer environment with dedicated high-bandwidth and low-latency interconnects, reliable and balanced memory and compute resources, the cloud offers dynamic and heterogenous resources that are loosely connected over an IP network. Large-scale symbolic execution tasks must therefore be adapted to a worker-queue model with centralized load balancing that rebalances tasks across a dynamic pool of compute resources, minimizing the amount of data exchanged between the workers and load balancers and practically eliminating any communication between workers. CloudSuite leverages Cloud9, software testing as a service software, to cover this category of applications.

</p>


<a href="./search.html">Web Search</a><p>

Web search engines, such as those powering Google and Microsoft Bing, index terabytes of data harvested from online
sources. To support a large number of concurrent latency-sensitive search queries against the index, the data is split 
into memory-residents shards, with each index serving node (ISN) responsible for processing requests to its own shard. 
A frontend machine sends index search requests to all ISNs in parallel, collects and sorts the responses, and delivers a 
formatted reply to the requesting client. Hundreds of unrelated search requests are handled by each ISN every second, 
with minimal locality; shards are therefore sized to fit into the memory of the ISNs to avoid reducing throughput and
degrading QoS due to disk I/O. To represent search engines, CloudSuite uses the Nutch/Lucene search engine stimulated
by a client representing real-world scenarios.
</p>

<a href="./web.html">Web Serving</a><p>

Web Serving is a main service in the cloud. Traditional web services with dynamic and static content are moved into the 
cloud to provide fault-tolerance and dynamic scalability by bringing up the needed number of servers behind a load balancer. 
Although many variants of the traditional web stack are used in the cloud (e.g., substituting Apache with other web server 
software or using other language interpreters in place of PHP), the underlying service architecture remains unchanged. 
Independent client requests are accepted by a stateless web server process which either directly serves static files from
disk or passes the request to a stateless middleware script, written in a high-level interpreted or byte-code compiled language, 
which is then responsible for producing dynamic content. All the state information is stored by the middleware in backend databases 
such as cloud NoSQL data stores or traditional relational SQL servers supported by key-value cache servers to achieve high throughput and low latency. 
CloudSuite leverages CloudStone running the PHP version of Olio, introducing minor modifications to the original application.
</p>


                <!-- end include/mainData/dataPicture.jsp -->