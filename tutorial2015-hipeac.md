---
layout: page
title: Server Benchmarking with CloudSuite 3.0
---


<p>
<ul>
<li><b>Where?</b> In conjuction with <a href="https://www.hipeac.net/2016/prague/schedule/#tutori">HiPEAC </a> in Prague, Czech Republic.</li>
<li><b>When?</b> January 20th, 2016.</li>
<li><b>Intended audience:</b> Academic/industrial researchers interested in scale-out datacenter workloads and their performance evaluation via both existing state- of-the-art servers and cycle-accurate simulation.</li>
<li><b>Team:</b> Alexander Daglis, Mario Paulo Drumond, Tapti Palit, Javier Picorel, Babak Falsafi, Mike Ferdman of EPFL and Stonybrook.</li>
<li><b>Keywords:</b> Scale-out workloads, server benchmarking, rigorous measurement methodologies, performance evaluation.</li>
<li><b>Registration:</b> Please follow the registration link on the <a href="https://www.hipeac.net/2016/prague/">conference web page</a>.</li>
</ul>
</p>

<br/>
<h2>Tutorial In Brief</h2>
<p>
Since its inception, CloudSuite (cloudsuite.ch) has emerged as a popular suite of benchmarks both in industry and among academics for scale-out server and services performance evaluation. The EuroCloud Server project blueprinted key optimizations in server SoCs based on the salient features of CloudSuite workloads that lead to an order of magnitude improvement in efficiency while preserving QoS. ARM-based server products (e.g., Cavium ThunderX) have now emerged following these guidelines and showcasing the improved efficiency.
</p>
<p>
CloudSuite 3.0 is a major enhancement over prior releases both in workloads and infrastructure. It includes benchmarks that represent massive data maniuplation with tight latency constraints such as in-memory data analytics using Apache Spark, a new real-time video streaming benchmark following today’s most popular video-sharing website setups, and a new web serving benchmark mirroring today’s multi-tier web server software stacks with a caching layer.
</p>
<p>
To ease the deployment of CloudSuite into private and public cloud systems, the benchmarks are integrated into the Docker container system and Google’s PerfKit Benchmarker. PerfKit helps at automate the process of benchmarking with a performance comparison across existing cloud server systems. CloudSuite 3.0 will be released and supported to run both on real hardware and a QEMU- based emulation platform.
</p>
<br/>



<h3>Organizers</h3>
<ul>
<li><a href="http://parsa.epfl.ch/~daglis">Alexandros Daglis</a> is a fourth year PhD student that works at EPFL under the supervision of Babak Falsafi. His research interests include rack-scale computing and datacenter architectures. His current focus is on system design for high performance remote memory access.</li>
<li><a href="http://parsa.epfl.ch/~picorel">Javier Picorel</a> is a fifth year PhD student working at EPFL under the supervision of Babak Falsafi. His research interests are in computer architecture, specially architectures and system support for processing-in-memory systems.</li>
<li><a href="http://parsa.epfl.ch/~drumond">Mario Drumond</a> is a second year PhD student that works at EPFL under the supervision of Babak Falsafi. His research interests include reconfigurable computing and computer architecture. His current focus is on using FPGAs to acelerate machine learning algorithms.</li>
<!--
<li> <a href="http://parsa.epfl.ch/~jevdjic">Djordje Jevdjic</a> is a fifth-year PhD candidate in the Parallel Systems Architecture Laboratory at EPFL, advised by Prof. Babak Falsafi. Djordje works on high-performance memory systems for servers, including on-chip DRAM caches and 3D-die stacking, with emphasis on locality and energy-efficiency.</li>
<li> <a href="http://parsa.epfl.ch/~kaynak">Cansu Kaynak</a> is a fifth-year PhD candidate in the Parallel Systems Architecture Laboratory at EPFL, advised by Prof. Babak Falsafi. Cansu's research focuses on high-performance memory systems to bridge the ever-increasing processor/memory performance gap. She is currently working on mitigating instruction-related stalls, a key performance bottleneck in server applications.</li> 
<li> <a href="http://parsa.epfl.ch/~volos">Stavros Volos</a> is a fifth-year PhD candidate in the Parallel Systems Architecture Laboratory at EPFL, advised by Prof. Babak Falsafi. Stavros's work focuses on energy-efficient memory systems for server applications, with emphasis on energy-efficient data movement.</li><
-->

</ul>


<h3>(Tentative) Agenda</h3>
<table cellspacing="8" border="0">
<tr id="row1"  >
<td width="20%" align="center">Time Slot</td><td align="center"> Topic</td><td align="center">Material</td>
</tr>
<tr align="center"><td>14:00 - 15:00</td><td>CloudSuite 3.0</td><td>Overview of CloudSuite 3.0</td>
</tr>
<tr align="center"><td>15:00 - 15:15</td>
<td><em>Coffee Break</em></td><td></td>
</tr>
<tr align="center"><td>15:15 - 16:15</td><td>CloudSuite on Real Hardware</td><td>Running CloudSuite on real hardware</td></tr>
<tr align="center"><td>16:15 - 16:30</td><td><em>Coffee break</em></td><td></td>
</tr>
<tr align="center"><td>16:30 - 17:30</td><td>CloudSuite Full-System Simulation</td><td>Introduction to Flexus and its interaction with the QEMU full-system simulator</td>
</table>



