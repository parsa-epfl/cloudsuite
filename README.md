# CloudSuite 3.0 #

**This branch is an archive where all CloudSuite 3.0 benchmarks are stored. All prebuilt images are available at [cloudsuite3][old] at dockerhub. If you're searching for CloudSuite 4.0, please checkout [master][master] branch.**

[CloudSuite][csp] is a benchmark suite for cloud services. The third release consists of eight applications that have 
been selected based on their popularity in today's datacenters. The benchmarks are based on real-world software 
stacks and represent real-world setups.



# Licensing #

CloudSuite's software components are all available as open-source software. All of the software components are governed by 
their own licensing terms. Researchers interested in using CloudSuite are required to fully understand and abide by the 
licensing terms of the various components. For more information, please refer to the [license page][csl].

# Deploying CloudSuite #

To ease the deployment of CloudSuite into private and public cloud systems, we provide docker images for all CloudSuite benchmarks 
(available [here][csb]). We are also integrating CloudSuite into Google's [PerfKit Benchmarker][pkb]. PerfKit helps at automating the process of 
benchmarking across existing cloud-server systems.

# Support #

We encourage CloudSuite users to use GitHub issues for requests for enhancements, questions or bug fixes.

[csp]: http://cloudsuite.ch "CloudSuite Page"
[csl]: http://cloudsuite.ch/pages/license/ "CloudSuite License"
[csb]: http://cloudsuite.ch/#download "CloudSuite Benchmarks"
[pkb]: https://github.com/GoogleCloudPlatform/PerfKitBenchmarker "Google's PerfKit Benchmarker"
[old]: https://hub.docker.com/orgs/cloudsuite3/repositories "CloudSuite3 on Dockerhub"
[master]: https://github.com/parsa-epfl/cloudsuite "CloudSuite Master"
