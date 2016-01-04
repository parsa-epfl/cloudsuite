FROM ubuntu

RUN apt-get install software-properties-common -y

RUN add-apt-repository ppa:webupd8team/java && apt-get update

RUN echo debconf shared/accepted-oracle-license-v1-1 select true | sudo debconf-set-selections \
 && echo debconf shared/accepted-oracle-license-v1-1 seen true | sudo debconf-set-selections 
RUN buildDeps='oracle-java7-installer python' \
 && set -x \
 && apt-get install -y $buildDeps --no-install-recommends \
 && apt-get autoremove && apt-get clean

RUN groupadd -r cassandra && useradd -r -g cassandra cassandra

RUN wget -q https://github.com/brianfrankcooper/YCSB/releases/download/0.3.0/ycsb-0.3.0.tar.gz -O /ycsb-0.3.0.tar.gz \
 && tar -xzf /ycsb-0.3.0.tar.gz && rm /ycsb-0.3.0.tar.gz && mv /ycsb-0.3.0 /ycsb \
 && chmod -R 775 /ycsb/workloads

USER cassandra 
