FROM ubuntu:18.04

RUN apt-get update -y && apt-get install -y --no-install-recommends openjdk-11-jre-headless \
	&& rm -rf /var/lib/apt/lists/*

ENV JAVA_HOME /usr/lib/jvm/java-11-openjdk-amd64
