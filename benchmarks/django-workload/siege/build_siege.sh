#!/bin/bash

echo "Building image for siege-webtier"
docker build --no-cache -t siege-webtier .
