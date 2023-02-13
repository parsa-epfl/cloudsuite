#!/bin/bash

ARGS=()
MODE="bash"
SCALE=30
WORKERS=4
SERVER_MEMORY=4096
INTERVAL=1
GET_RATIO=0.8
CONNECTION=200
RPS=10000

while (( ${#@} )); do
  case ${1} in
    --m=*)       MODE=${1#*=} ;;
    --S=*)       SCALE=${1#*=} ;;
    --w=*)       WORKERS=${1#*=} ;;
    --D=*)       SERVER_MEMORY=${1#*=} ;;
    --T=*)       INTERVAL=${1#*=} ;;
    --g=*)       GET_RATIO=${1#*=} ;;
    --c=*)       CONNECTION=${1#*=} ;;
    --r=*)       RPS=${1#*=} ;;
    *)           ARGS+=(${1}) ;;
  esac

  shift
done

set -- ${ARGS[@]}

echo "mode: ${MODE}, scale: ${SCALE}, workers: ${WORKERS}, server_memory: ${SERVER_MEMORY}, interval: ${INTERVAL}, get_ratio: ${GET_RATIO}, connections: ${CONNECTION}, rps: ${RPS}"

if [ "$MODE" = 'S&W' ]; then
        echo "scale and warmup"
        /usr/src/memcached/memcached_client/loader \
                -a /usr/src/memcached/twitter_dataset/twitter_dataset_unscaled \
                -o /usr/src/memcached/twitter_dataset/twitter_dataset_${SCALE}x \
                -s /usr/src/memcached/memcached_client/docker_servers/docker_servers.txt \
                -w ${WORKERS} -S ${SCALE} -D ${SERVER_MEMORY} -j -T ${INTERVAL}
elif [ "$MODE" = 'W' ]; then
        echo "warmup"
        /usr/src/memcached/memcached_client/loader \
                -a /usr/src/memcached/twitter_dataset/twitter_dataset_${SCALE}x \
                -s /usr/src/memcached/memcached_client/docker_servers/docker_servers.txt \
                -w ${WORKERS} -S 1 -D ${SERVER_MEMORY} -j -T ${INTERVAL}
elif [ "$MODE" = 'TH' ]; then
        echo "max throughput"
        /usr/src/memcached/memcached_client/loader \
                -a /usr/src/memcached/twitter_dataset/twitter_dataset_${SCALE}x \
                -s /usr/src/memcached/memcached_client/docker_servers/docker_servers.txt \
                -g ${GET_RATIO} -w ${WORKERS} -c ${CONNECTION} -T ${INTERVAL}
elif [ "$MODE" = 'RPS' ]; then
        echo "RPS"
        /usr/src/memcached/memcached_client/loader \
                -a /usr/src/memcached/twitter_dataset/twitter_dataset_${SCALE}x \
                -s /usr/src/memcached/memcached_client/docker_servers/docker_servers.txt \
                -g ${GET_RATIO} -w ${WORKERS} -c ${CONNECTION} -T ${INTERVAL} -e -r ${RPS}
elif [ "$MODE" = "bash" ]; then
        # bash
        exec /bin/bash
fi
