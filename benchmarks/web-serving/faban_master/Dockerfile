FROM phusion/baseimage:0.10.0
LABEL maintainer="mark.sutherland@epfl.ch"
RUN apt-get update && apt-get install -y \
	ant \
	build-essential \
    curl \
	openjdk-8-jdk \
	wget

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV JAVA_HOME /usr/lib/jvm/java-8-openjdk-amd64
ENV FABAN_HOME /faban

# Setup Faban 

RUN wget http://faban.org/downloads/faban-kit-1.4.tar.gz && tar xzf faban-kit-1.4.tar.gz
COPY files/web20_benchmark /web20_benchmark
WORKDIR /web20_benchmark

# Build the Faban benchmark and the user-generation tool
RUN ant deploy.jar && ant usergen-jar

# Copy files to their required locations
RUN cp /web20_benchmark/build/Web20Driver.jar /faban/benchmarks/ && mkdir /etc/service/faban_master
COPY files/usersetup.properties /faban/usersetup.properties

# Copies faban bootstrap out of root directory for use with my_init
COPY files/bootstrap_master.sh /etc/service/faban_master/run 
COPY files/catalina_foreground.sh /faban/master/bin/catalina_foreground.sh
RUN chmod +x /etc/service/faban_master/run
#RUN cat /faban/master/bin/startup.sh >> /etc/service/faban_master/run && chmod +x /etc/service/faban_master/run
#ADD bootstrap_master.sh /etc/bootstrap.sh
#RUN chown root:root /etc/bootstrap.sh
#RUN chmod 700 /etc/bootstrap.sh

#ENTRYPOINT ["/etc/bootstrap.sh"]
CMD ["/sbin/my_init"]
