FROM cloudsuite/faban:1.4
LABEL maintainer="Mark Sutherland <mark.sutherland@epfl.ch>"

RUN apt-get update && apt-get install -y \
	ant \
	build-essential \
        curl \
	wget

# Setup Faban 



COPY files/web20_benchmark /web20_benchmark

WORKDIR /web20_benchmark


# Build the Faban benchmark and the user-generation tool
RUN sed -i "s@faban\.home.*@faban.home=$FABAN_HOME@" /web20_benchmark/build.properties
RUN ant deploy.jar
RUN ant usergen-jar

# Copy files to their required locations
RUN cp /web20_benchmark/build/Web20Driver.jar $FABAN_HOME/benchmarks/

COPY files/usersetup.properties $FABAN_HOME/usersetup.properties

ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

ENTRYPOINT ["/etc/bootstrap.sh"]
