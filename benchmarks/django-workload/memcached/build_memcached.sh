#!/bin/bash

echo "Building image for memcached-webtier"
docker build --no-cache -t memcached-webtier .

