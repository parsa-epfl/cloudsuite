# zend-servicemanager

Master:
[![Build Status](https://secure.travis-ci.org/zendframework/zend-servicemanager.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-servicemanager)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-servicemanager/badge.svg?branch=master)](https://coveralls.io/github/zendframework/zend-servicemanager?branch=master)
Develop:
[![Build Status](https://secure.travis-ci.org/zendframework/zend-servicemanager.svg?branch=develop)](https://secure.travis-ci.org/zendframework/zend-servicemanager)
[![Coverage Status](https://coveralls.io/repos/github/zendframework/zend-servicemanager/badge.svg?branch=develop)](https://coveralls.io/github/zendframework/zend-servicemanager?branch=develop)

The Service Locator design pattern is implemented by the `Zend\ServiceManager`
component. The Service Locator is a service/object locator, tasked with
retrieving other objects.

- File issues at https://github.com/zendframework/zend-servicemanager/issues
- [Online documentation](https://docs.zendframework.com/zend-servicemanager)
- [Documentation source files](docs/book/)

## Benchmarks

We provide scripts for benchmarking zend-servicemanager using the
[PHPBench](https://github.com/phpbench/phpbench) framework; these can be
found in the `benchmarks/` directory.

To execute the benchmarks you can run the following command:

```bash
$ vendor/bin/phpbench run --report=aggregate
```
