#!/bin/sh

mkdir -p /root/VideoSet
cd /root/VideoSet

mkdir -p 240p; cd 240p
for i in `seq 1 9`
do
    curl -O https://cloudsuite.ch/download/media_streaming/VideoSet/240p/240_${i}.mp4
done
cd ..

mkdir -p 360p; cd 360p
for i in `seq 1 9`
do
    curl -O https://cloudsuite.ch/download/media_streaming/VideoSet/360p/360_${i}.mp4
done
cd ..

mkdir -p 480p; cd 480p
for i in `seq 1 9`
do
    curl -O https://cloudsuite.ch/download/media_streaming/VideoSet/480p/480_${i}.mp4
done
cd ..

mkdir -p 720p; cd 720p
for i in `seq 1 9`
do
    curl -O https://cloudsuite.ch/download/media_streaming/VideoSet/720p/720_${i}.mp4
done
cd ..


