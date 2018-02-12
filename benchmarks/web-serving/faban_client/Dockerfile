FROM ubuntu:16.04
LABEL maintainer="mark.sutherland@epfl.ch"

RUN apt-get update && apt-get install -y \
	ant \
	build-essential \
    curl \
	openjdk-8-jdk \
	wget

ENV JAVA_HOME /usr/lib/jvm/java-8-openjdk-amd64
ENV FABAN_HOME /faban

# Setup Faban agent
COPY files/faban-agent-jre1.8.0.tgz /faban-agent-jre1.8.0.tgz
RUN tar xzf faban-agent-jre1.8.0.tgz 

ENTRYPOINT ["/faban/bin/agent"]
