#!/bin/bash

echo "Building image for graphite-webtier"
docker build --no-cache -t graphite-webtier .
