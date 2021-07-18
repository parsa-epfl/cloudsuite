#!/bin/bash

for arch in amd64 arm64 riscv64; do
    docker buildx build --platform=linux/${arch} -t cloudsuitetest/java:openjdk11_${arch} -f Dockerfile.${arch} .
done
