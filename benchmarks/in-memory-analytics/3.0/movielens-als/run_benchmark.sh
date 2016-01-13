#!/usr/bin/env bash

BENCHMARK_DIR=/benchmarks

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

exec ${SPARK_HOME}/bin/spark-submit --class MovieLensALS "$@" \
       ${BENCHMARK_DIR}/movielens-als/movielens-als-1.0.jar $DATASET $RATINGS

