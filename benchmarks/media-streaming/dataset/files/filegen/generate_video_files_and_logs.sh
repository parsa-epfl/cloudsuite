#!/bin/bash -xe

PARAMS=$1
VIDEOS=$2
mkdir -p "$VIDEOS"

cd /filegen

for paramfile in "$PARAMS/"*; do
	BN=$(basename "$paramfile")
	cp "$paramfile" filegen_param.conf
	./make_zipf
	./gen_fileset "$VIDEOS/full-$BN-" ./video_files.txt
done
