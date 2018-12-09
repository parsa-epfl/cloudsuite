#!/bin/bash

echo "Building image for cassandra-webtier"
docker build --no-cache -t cassandra-webtier .
