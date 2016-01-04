FROM ubuntu:14.04
MAINTAINER Alexandros Daglis <alexandros.daglis@epfl.ch>

RUN apt-get update && apt-get install -y build-essential 

#create data dir for videos
RUN mkdir /videos
RUN mkdir /videos/logs

#build data gen utilities
COPY files/filegen /filegen/
COPY files/filegen_params /filegen_params/
WORKDIR /filegen
RUN make -B make_zipf gen_fileset

#build the dataset
RUN /filegen/generate_video_files_and_logs.sh /filegen_params /videos
RUN mv cl* /videos/logs/. 

VOLUME /videos
