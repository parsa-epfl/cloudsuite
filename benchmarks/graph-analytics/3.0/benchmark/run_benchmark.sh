#!/usr/bin/env bash

BENCHMARK_DIR=/benchmarks
WORKLOAD_NAME=pagerank

read -r -d '' USAGE <<EOS
Usage: graph-analytics [SPARK_OPTIONS]

  SPARK_OPTIONS are passed on to spark-submit.
EOS

${SPARK_HOME}/bin/spark-submit "$@" \
  --class "GraphAnalytics" \
  ${BENCHMARK_DIR}/graph-analytics_2.10-1.0.jar \
  -app="${WORKLOAD_NAME}"
