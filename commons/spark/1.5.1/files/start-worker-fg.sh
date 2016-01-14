#!/usr/bin/env bash

# Starts Spark worker on the machine this script is executed on.

usage="Usage: $(basename $0) <spark-master-URL> where <spark-master-URL> is like spark://master-hostname:7077"

if [ $# -lt 1 ]; then
  echo $usage
  exit 1
fi

MASTER=$1
shift

. "$SPARK_HOME/sbin/spark-config.sh"

. "$SPARK_HOME/bin/load-spark-env.sh"

if [ "$SPARK_WORKER_WEBUI_PORT" = "" ]; then
  SPARK_WORKER_WEBUI_PORT=8080
fi

exec "$SPARK_HOME"/bin/spark-class org.apache.spark.deploy.worker.Worker \
  --webui-port $SPARK_WORKER_WEBUI_PORT $MASTER "$@"

