#!/bin/bash

echo "Building image for cassandra-webtier"
sudo docker build --no-cache -t cassandra-webtier .
