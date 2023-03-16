FROM cloudsuite/base-os:ubuntu

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get install make wget build-essential cmake -y

RUN wget https://downloads.xiph.org/releases/ogg/libogg-1.3.4.tar.gz \
    && tar -zxvf libogg-1.3.4.tar.gz \
    && cd libogg-1.3.4 \
    && ./configure \
    && make \
    && make install \
    && cp /usr/local/lib/libogg.so.0.8.4 /usr/lib/$(uname -m)-linux-gnu/libogg.so.0.8.4 \
    && ln -s /usr/lib/$(uname -m)-linux-gnu/libogg.so.0.8.4 /usr/lib/$(uname -m)-linux-gnu/libogg.so.0 \
        && mkdir build \
        && cd build \
        && cmake -G "Unix Makefiles" -DCMAKE_BUILD_TYPE=Release -DBUILD_TESTING=ON -DCPACK_PACKAGE_CONTACT="Xiph.Org Foundation" -DCPACK_PACKAGE_NAME=libogg0 .. \
        && cmake --build . \
        && cpack -G DEB \
        && dpkg -i /libogg-1.3.4/build/libogg0-1.3.4-Linux.deb

RUN apt-get update && apt-get upgrade -y && apt-get autoremove -y \
        && apt-get install -y --no-install-recommends \
	libavcodec58 libavfilter7 \
	libavdevice58 libavformat58 \
	libchromaprint1 libflac8 \
	libvorbisenc2  \
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
