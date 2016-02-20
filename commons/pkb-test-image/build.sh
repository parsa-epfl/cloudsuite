#!/bin/sh

cd $(dirname $0)

rm -rf keys
mkdir keys
ssh-keygen -t rsa -N '' -f keys/sshkey

docker build --rm -t pkbtest .

