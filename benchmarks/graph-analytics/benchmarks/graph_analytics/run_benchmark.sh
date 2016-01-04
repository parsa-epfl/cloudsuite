#!/bin/sh

BENCHMARK_DIR=/benchmarks/graph_analytics
WORKLOAD_NAME=pagerank

${SPARK_HOME}/bin/spark-submit \
  --driver-memory 1g \
  --executor-memory 1g \
  --class "GraphAnalytics" \
  --master spark://master:7077 \
  ${BENCHMARK_DIR}/graph-analytics_2.10-1.0.jar \
  -app="${WORKLOAD_NAME}"
