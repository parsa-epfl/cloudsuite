---
layout: default
title: Home
---
<!-- #icon: flaticon-user7 -->

<div id="content_outline">
<div id="content-projects"> 

<h1><b>News</b></h1>

<ul style="list-style: none; padding-left:0;">

<li> <b> Dec 2015: </b> <br> A tutorial, <a href="./tutorial2015-hipeac/index.html">Server Benchmarking with CloudSuite 3.0</a>, will be presented on January, 20th.</li>
<br>
<li> <b> Feb 2015: </b> <br> A two-day tutorial, <a href="http://parsa.epfl.ch/cloudsuite/CloudSuite-Flexus-epfl15.html">Rigorous and Practical Server Design Evaluation</a>, will be presented on February 2nd-3rd at EPFL. Here are the tutorial slides used on the <a href="http://parsa.epfl.ch/cloudsuite/docs/CloudSuite2.0-on-Flexus-epfl-15_day1.pdf">first day</a> and the <a href="http://parsa.epfl.ch/cloudsuite/docs/CloudSuite2.0-on-Flexus-epfl-15_day2.pdf">second day</a>.</li>
<br>
<!-- <li> <b> Mar 2014: </b> <br> CloudSuite 2.0 will be presented at the tutorial <a href="http://parsa.epfl.ch/cloudsuite/ispass14-tutorial">Rigorous and Practical Server Design Evaluation</a>. The tutorial will be held in conjuction with <a href="http://ispass.org/ispass2014/">ISPASS 2014 </a> in Monterey. Here are the tutorial <a href="docs/CloudSuite2.0-on-Flexus-ispass14.pdf">slides.</a></li>
<br>
<li> <b> Mar 2013: </b> <br> CloudSuite 2.0 is released and presented at the tutorial <a href="http://isca2013.eew.technion.ac.il/">ISCA 2013</a> in Tel Aviv.  Here are the tutorial <a href="docs/CloudSuite2.0-on-Flexus-isca13.pdf">slides</a> and the <a href="docs/deploy_cloudsuite.pdf">guidelines</a> for using the released Simics images.</li>
<li> <b> Jun 2013: </b> <br> CloudSuite 2.0 is be released in June 2013, and presented at the tutorial <a href="http://parsa.epfl.ch/cloudsuite/isca13-tutorial.html">CloudSuite 2.0 on Flexus</a>. The tutorial will be held in conjuction with <a href="http://isca2013.eew.technion.ac.il/">ISCA 2013</a> in Tel Aviv.</li> </br>
<br>
<li> <b> Jun 2012: </b> <br> A full-day tutorial, <a href="http://parsa.epfl.ch/cloudsuite/isca12-tutorial.html">CloudSuite on Flexus</a>, will be presented at ISCA 2012 in Portland, Oregon.</li></br>
</ul> -->


 <h1>CloudSuite 3.0</h1>

                        <!-- start include/mainData/dataPicture.jsp -->

<p>CloudSuite is a benchmark suite for emerging scale-out applications. The third release consists of eight applications that have been selected based on their popularity in today's datacenters. The benchmarks are based on real-world software stacks and represent real-world setups.

</p>



<h1>Motivation</h1>

<p>
Cloud computing is emerging as a dominant computing platform for providing scalable online services to a global client base. Today's popular online services (e.g., web search, social networking, and business analytics) are characterized by massive working sets, high degrees of parallelism, and real-time constraints. These characteristics set scale-out applications apart from desktop (SPEC), parallel (PARSEC), and traditional commercial server applications. In order to stimulate research in the field of cloud and data-centric computing, we have created CloudSuite, a benchmark suite based on real-world online services. 
</p>
<p>
CloudSuite covers a broad range of application categories commonly found in today's datacenters. The first release includes data analytics, data serving, media streaming, large-scale and computation-intensive tasks, web search, and web serving. The second release also includes graph analytics and data caching.</a>.
</p>
<h1>CloudSuite on Flexus</h1>
<p>
We provide Docker images for all CloudSuite benchmarks (available <a href="./benchmarks">here</a>). In addition to these Docker images, we have also prepared--and provide upon request--CloudSuite images running on the Simics full-system simulator, to facilitate micro-architectural simulation of CloudSuite applications. The Simics images allow the simulation of CloudSuite applications running on up to 64 cores and were released at our ISCA 2012 tutorial: "CloudSuite on Flexus".
</p>
<p>
 To get a copy of the CloudSuite images, please send an email to cloudsuite-admin(at)groupes.epfl.ch; you will be provided with an account to access and download the images. Please note that these images are based on the SPARC v9 ISA, running the Solaris OS, and are only compatible with Simics. 
</p>
</p>
<h1>Usage and licenses of CloudSuite</h1>
<p>CloudSuite is available for researchers interested in pursuing research in the field of cloud computing and datacenters. CloudSuite's software components are all available as open-source software. All of the software components are governed by their own licensing terms. Researchers interested in using CloudSuite are required to fully understand and abide by the licensing terms of the various components. For more information, please refer to the <a href="./licenses">license page</a>.</p>

<p>For a microarchitectural characterization of the benchmarks, please see the following ASPLOS 2012 paper: <a href="./publications">"Clearing the Clouds: A Study of Emerging Scale-out Workloads on Modern Hardware"</a></p>

<br/>

                <!-- end include/mainData/dataPicture.jsp -->
