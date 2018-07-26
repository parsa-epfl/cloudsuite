#!/bin/bash

for Dockerfile in $(git ls-files | grep Dockerfile); do
    sed -i 's@FROM ubuntu@FROM arm64v8/ubuntu@g' $Dockerfile
done
