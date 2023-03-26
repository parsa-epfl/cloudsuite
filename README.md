# CloudSuite 4.0 #

**This branch contains the release of CloudSuite v4.0. If you are looking for CloudSuite 3, please checkout the [CSv3][CSv3] branch.**

[CloudSuite][csp] is a benchmark suite for cloud services. The fourth release consists of eight first-party applications that have been selected based on their popularity in today's datacenters. The benchmarks are based on real-world software stacks and represent real-world setups. 

CloudSuite 4.0 includes a thorough software stack update and bug fixes for all workloads. It also includes support for all workloads on ARM to follow the rising trend of the ARM server market. It also features detailed guidelines for tuning and running the workloads in representative states, facilitating ease of use.

# How to Run #

For more details on how to run the workloads, please follow each workload's documentation:

- [Data Analytics](docs/benchmarks/data-analytics.md)
- [Data Caching](docs/benchmarks/data-caching.md)
- [Data Serving](docs/benchmarks/data-serving.md)
- [Graph Analytics](docs/benchmarks/graph-analytics.md)
- [In-memory Analytics](docs/benchmarks/in-memory-analytics.md)
- [Media Streaming](docs/benchmarks/media-streaming.md)
- [Web Search](docs/benchmarks/web-search.md)
- [Web Serving](docs/benchmarks/web-serving.md)

To ease the deployment of CloudSuite into private and public cloud systems, we provide docker images for all CloudSuite benchmarks 
(available [here][csb]). 

# Workload Status #
To see which workloads are currently functioning on the new architectures, you can find the status matrix [here][status_pg].

# Licensing #

CloudSuite's software components are all available as open-source software. All of the software components are governed by 
their own licensing terms. Researchers interested in using CloudSuite are required to fully understand and abide by the 
licensing terms of the various components. For more information, please refer to the [license page][csl].

# Support #

We encourage CloudSuite users to use GitHub issues to request for enhancements, questions or bug fixes.

[csp]: http://cloudsuite.ch "CloudSuite Page"
[csl]: http://cloudsuite.ch/pages/license/ "CloudSuite License"
[csb]: http://cloudsuite.ch/#download "CloudSuite Benchmarks"
[CSv3]: https://github.com/parsa-epfl/cloudsuite/tree/CSv3 "CloudSuite v3"
[status_pg]: https://github.com/parsa-epfl/cloudsuite/wiki/CloudSuite-4.0-Workload-Status-Matrix
