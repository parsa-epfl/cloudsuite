FROM ubuntu:18.04

RUN apt-get update -y && apt-get install -y --no-install-recommends openjdk-8-jdk-headless \
	&& rm -rf /var/lib/apt/lists/*

ENV JAVA_HOME /usr/lib/jvm/java-8-openjdk-amd64
