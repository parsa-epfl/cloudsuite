FROM cloudsuite/debian:base-os
MAINTAINER Alexandros Daglis <alexandros.daglis@epfl.ch>

RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends \
	software-properties-common \
	build-essential \
	curl \
	wget \
	yasm \
	ffmpeg \
	&& rm -rf /var/lib/apt/lists/*

COPY files /root/
RUN chmod 755 /root/download_video_files.sh
RUN /root/download_video_files.sh

ADD bootstrap.sh /root/bootstrap.sh
RUN chown root:root /root/bootstrap.sh
RUN chmod 700 /root/bootstrap.sh

VOLUME ["/videos"]

ENTRYPOINT ["/root/bootstrap.sh"]
