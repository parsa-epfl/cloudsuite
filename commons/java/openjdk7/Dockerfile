FROM ubuntu:14.04

RUN apt-get update -y && apt-get install -y --no-install-recommends openjdk-7-jdk-headless \
	&& rm -rf /var/lib/apt/lists/*

ENV JAVA_HOME /usr/lib/jvm/java-7-openjdk-amd64
