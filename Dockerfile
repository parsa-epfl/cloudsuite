FROM ubuntu:14.04

RUN apt-get update -y && apt-get install -y --no-install-recommends openjdk-7-jre-headless \
	&& rm -rf /var/lib/apt/lists/*
