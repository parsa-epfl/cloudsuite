#!/bin/bash

VIDEOS_DIR=$1
mkdir -p "$VIDEOS_DIR/logs"

for paramfile in params/*; do
  cp "$paramfile" filegen_param.conf
  ./make_zipf
  ./gen_fileset "$VIDEOS_DIR/full-$(basename $paramfile)-" video_files.txt
  cp cl* "$VIDEOS_DIR/logs"
done
