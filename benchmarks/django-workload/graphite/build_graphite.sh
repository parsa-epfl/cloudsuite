#!/bin/bash

echo "Building image for graphite-webtier"
sudo docker build --no-cache -t graphite-webtier .
