# CloudSuite 4.0 #

**This branch is WIP and contains the upcoming release of CloudSuite v4.0. If you are looking for CloudSuite 3, please checkout the [CSv3][CSv3] branch.**

[CloudSuite][csp] is a benchmark suite for cloud services. The fourth release consists of ten applications that have 
been selected based on their popularity in today's datacenters. The benchmarks are based on real-world software 
stacks and represent real-world setups. In v4.0, we have added multi-architecture support to Cloudsuite, so that the
workloads can be run on processors using x86, ARM, and RISC-V architectures.

# How to Run #

For more details on how to run the workloads, please follow the individual workload documentation here:

- [Data Analytics](docs/benchmarks/data-analytics.md)
- [Data Caching](docs/benchmarks/data-caching.md)
- [Data Serving](docs/benchmarks/data-serving.md)
- [Graph Analytics](docs/benchmarks/graph-analytics.md)
- [In-memory Analytics](docs/benchmarks/in-memory-analytics.md)
- [Media Streaming](docs/benchmarks/media-streaming.md)
- [Web Search](docs/benchmarks/web-search.md)
- [Web Serving](docs/benchmarks/web-serving.md)

# Workload Status #
To see which workloads are currently functioning on the new architectures, you can find the status matrix [here][status_pg].

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
[CSv3]: https://github.com/parsa-epfl/cloudsuite/tree/CSv3 "CloudSuite v3"
[status_pg]: https://github.com/parsa-epfl/cloudsuite/wiki/CloudSuite-4.0-Workload-Status-Matrix
