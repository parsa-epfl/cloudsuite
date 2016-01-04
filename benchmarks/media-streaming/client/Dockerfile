# Container description
FROM ubuntu:14.04
#Original maintainer: MAINTAINER Tapti Palit <tpalit@cs.stonybrook.edu>
MAINTAINER Alexandros Daglis <alexandros.daglis@epfl.ch>

RUN apt-get update && apt-get install -y \
	build-essential \
	git \
	libssl-dev

# Build utilities
COPY files/videoperf/ /videoperf/
COPY files/run /videoperf/run
WORKDIR /videoperf
RUN ./configure
RUN make

COPY files/docker-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

#CMD bash
