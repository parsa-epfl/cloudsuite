FROM cloudsuite/faban:1.4

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
COPY files/users.list $FABAN_HOME/users.list

RUN chown root:root bin/entrypoint.sh
RUN chmod 700 bin/entrypoint.sh

ENTRYPOINT ["bin/entrypoint.sh"]
