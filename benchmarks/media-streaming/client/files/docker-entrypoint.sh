#!/bin/bash

cp -r /videos/logs /videoperf/.
export LOGS=$(echo /videoperf/logs/cl* | sed -e 's/ /,/g')
bash
