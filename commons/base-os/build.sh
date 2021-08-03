#!/bin/bash

for arch in amd64 arm64 riscv64; do
    docker buildx build --platform=linux/${arch} -t cloudsuite/debian:${arch} -f Dockerfile.${arch} --push .
done

docker manifest create --amend cloudsuite/debian:base-os cloudsuite/debian:amd64 cloudsuite/debian:arm64 cloudsuite/debian:riscv64
docker manifest push cloudsuite/debian:base-os
