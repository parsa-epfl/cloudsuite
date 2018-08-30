#!/bin/bash

servers=2*$1
threads=5*$
memory=3584

 ./loader -a ../distributions/complete/twitter_size_complete -o ../distributions/complete/twitter_size_complete_40x -s localhost -c 50 -w 2 -S 40 -W 2 -T 1 -j -D 3584
