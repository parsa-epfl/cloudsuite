FROM cloudsuite/base-os:debian

RUN apt-get update -y && apt-get install -y --no-install-recommends openjdk-11-jdk-headless \
        && rm -rf /var/lib/apt/lists/*

ARG EXTERNAL_ARG
ENV JAVA_HOME=${EXTERNAL_ARG}
RUN echo ${JAVA_HOME}
