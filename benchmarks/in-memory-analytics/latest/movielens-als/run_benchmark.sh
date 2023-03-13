#!/usr/bin/env bash

read -r -d '' USAGE << EOS
Usage: in-memory-analytics DATASET RATINGS [SPARK_OPTIONS]

  DATASET is the dataset directory (e.g. /data/ml-latest-small).
  RATINGS is the rating file (e.g. /data/myratings.csv).
  SPARK_OPTIONS are passed on to spark-submit.
EOS

if [[ $# -lt 2 ]]; then
  echo "$USAGE"
  exit 1
fi

DATASET=$1
shift
RATINGS=$1
shift

echo "Executing with: DATASET:${DATASET} RATINGS:${RATINGS}"
echo "                SPARK_OPTIONS:"$@""
exec ${SPARK_HOME}/bin/spark-submit --class MovieLensALS "$@" \
       ${BENCHMARK_JAR} $DATASET $RATINGS

