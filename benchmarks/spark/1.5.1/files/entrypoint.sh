#!/usr/bin/env bash

read -r -d '' USAGE << EOS
Usage: spark master|worker|submit|shell [SPARK_OPTIONS]

Commands:
  master       Start Spark master (cluster manager)
  worker       Start Spark worker
  submit       Submit a job to Spark master
  shell        Run an interactive Spark shell

  SPARK_OPTIONS are passed to commands.
EOS

DIR=$(cd $(dirname "$0") && pwd)

if [[ $# -eq 0 ]]; then
  echo "$USAGE"
  exit 1
fi

CMD=
case $1 in
  master) 
    CMD=$DIR/start-master-fg.sh
    shift
    ;;
  worker) 
    CMD=$DIR/start-worker-fg.sh
    shift
    ;;
  submit)
    CMD=${SPARK_HOME}/bin/spark-submit
    shift
    ;;
  shell)
    CMD=${SPARK_HOME}/bin/spark-shell
    shift
    ;;
esac

exec $CMD "$@"

