#!/bin/sh

BENCHMARK_DIR=/benchmarks/movielens-als
DATA_DIR=/data
TRAINING_SET=ml-latest-small

${SPARK_HOME}/bin/spark-submit --driver-memory 1g --executor-memory 1g --class MovieLensALS \
  --master spark://spark-master:7077 \
  ${BENCHMARK_DIR}/movielens-als-1.0.jar \
  ${DATA_DIR}/${TRAINING_SET} \
  ${DATA_DIR}/myratings.csv

