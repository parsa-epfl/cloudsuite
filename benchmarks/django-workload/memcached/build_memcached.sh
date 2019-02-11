#!/bin/bash

echo "Building image for memcached-webtier"
sudo docker build --no-cache -t memcached-webtier .

