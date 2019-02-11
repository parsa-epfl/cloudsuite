#!/bin/bash

echo "Building image for siege-webtier"
sudo docker build --no-cache -t siege-webtier .
