#!/usr/bin/env bash

read -r -d '' USAGE << EOS
Usage: graph-analytics [SPARK_OPTIONS]
  SPARK_OPTIONS are passed on to spark-submit.
EOS

if [ -n "$INPUT_FILE" ]; then
  ARG_FILENAME=-file="${INPUT_FILE}"
fi

if [ -n "$WORKLOAD_NAME" ]; then
  ARG_WORKLOAD=-app="${WORKLOAD_NAME}"
fi

if [ -n "$NUM_ITER" ]; then
  ARG_NITERS=-niter="${NUM_ITER}"
fi

BENCHMARK_JAR=/root/graph-analytics-2.0.jar

echo "Executing with: $ARG_FILENAME $ARG_WORKLOAD $ARG_NITERS"
echo "                SPARK_OPTIONS:"$@""
exec ${SPARK_HOME}/bin/spark-submit --class GraphAnalytics "$@" \
    ${BENCHMARK_JAR} $ARG_FILENAME $ARG_WORKLOAD $ARG_NITERS
