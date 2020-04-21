FROM cloudsuite/debian:base-os
MAINTAINER Alexandros Daglis <alexandros.daglis@epfl.ch>

RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends \
	software-properties-common \
	build-essential \
	curl \
	wget \
	yasm \
	&& rm -rf /var/lib/apt/lists/*

RUN wget --progress=bar:force -O /tmp/ffmpeg-4.1.5.tar.bz2 http://ffmpeg.org/releases/ffmpeg-4.1.5.tar.bz2 \
	&& cd /tmp \
	&& tar xvjf ffmpeg-4.1.5.tar.bz2 \ 
	&& cd ffmpeg-4.1.5 \
	&& ./configure \
	&& make -j$(nproc) \
	&& make install \
	&& rm -rf /tmp/ffmpeg-4.1.5.tar.bz2

COPY files /root/
RUN chmod 755 /root/download_video_files.sh
RUN /root/download_video_files.sh

ADD bootstrap.sh /root/bootstrap.sh
RUN chown root:root /root/bootstrap.sh
RUN chmod 700 /root/bootstrap.sh

VOLUME ["/videos"]

ENTRYPOINT ["/root/bootstrap.sh"]
