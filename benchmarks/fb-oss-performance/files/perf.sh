#!/bin/sh

set -xe

# Switch to scripts dir.
cd "$(dirname "$0")"
ARGS="$@"

echo "Running as $(whoami)"

HHVM_PID="$(pgrep -xn 'hhvm')"
PREV_PID=`expr $HHVM_PID - 1`
if [ ! -f /tmp/perf-$HHVM_PID.map ] && [ -f /tmp/perf-$PREV_PID.map ]; then
    HHVM_PID=$PREV_PID
fi
OSS_DIR=$(pwd)

echo "The first arg is the output directory."
echo "The remaining args are passed along to perf record."
echo "Running perf on HHVM pid: $HHVM_PID"

# Go to repo root.
cd "$OSS_DIR/.."
nohup sh -xec "timeout --signal INT 30s perf stat -ddd -a -v $ARGS -p $HHVM_PID" >nohup.out 2>&1 &
