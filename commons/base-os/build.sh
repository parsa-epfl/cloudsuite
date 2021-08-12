#!/bin/bash

for arch in amd64 arm64 riscv64; do
    docker buildx build --platform=linux/${arch} -t cloudsuite/base-os:${arch} -f Dockerfile.${arch} --push .
done

docker manifest create --amend cloudsuite/base-os:debian cloudsuite/base-os:amd64 cloudsuite/base-os:arm64 cloudsuite/base-os:riscv64
docker manifest push cloudsuite/base-os:debian
