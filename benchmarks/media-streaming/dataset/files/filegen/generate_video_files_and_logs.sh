#!/bin/bash

VIDEOS_DIR=$1
VIDEO_SET=$2
LIBRARY_SIZE=$3
SESSIONS_SIZE=$4

mkdir -p "$VIDEOS_DIR/logs"
touch "$VIDEOS_DIR/test_videos.js"
chmod 744 "$VIDEOS_DIR/test_videos.js"
for paramfile in params/*; do
        mkdir /tmp/textpaths
        cp "$paramfile" filegen_param.conf

        if [ ! -z "$LIBRARY_SIZE" ]; then
                filename=filegen_param.conf
                test_pattern=library_size

                while IFS='' read -r line || [[ -n "$line" ]]; do
                        if [[ $line =~ $test_pattern ]]; then
                                param_to_search_and_replace=$line
                        fi
                done < "$filename"

                old_size="$(cut -d'=' -f2 <<<"$param_to_search_and_replace")"

                if [ "$LIBRARY_SIZE" -lt 52 ]
                then
                        multiplication_factor=`expr 520 \* $LIBRARY_SIZE`
                else
                        multiplication_factor=`expr 500 \* $LIBRARY_SIZE`
                fi

                new_size=$[$multiplication_factor / 151]
                new_size=$[$new_size + 1]
                sed -i "s/$test_pattern=$old_size/$test_pattern=$new_size/g" $filename

        fi

        if [ ! -z "$SESSIONS_SIZE" ]; then
                filename=filegen_param.conf
                test_pattern=num_log_sessions

                while IFS='' read -r line || [[ -n "$line" ]]; do
                        if [[ $line =~ $test_pattern ]]; then
                                param_to_search_and_replace=$line
                        fi
                done < "$filename"

                old_size="$(cut -d'=' -f2 <<<"$param_to_search_and_replace")"
                new_size=${SESSIONS_SIZE}
                sed -i "s/$test_pattern=$old_size/$test_pattern=$new_size/g" $filename

        fi

        ./make_zipf
        python3 ./video_gen.py -p filegen_param.conf -v video_files.txt -s "$VIDEO_SET" -o "$VIDEOS_DIR/"
        cp cl* "$VIDEOS_DIR/logs"
        rm -rf /tmp/textpaths/*
        rmdir /tmp/textpaths
done
