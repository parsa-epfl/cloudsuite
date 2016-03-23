#!/bin/bash

output_dir="/output"

nFiles=0
nRequests=0
nReplies=0
replyRateAcc=0
replyTimeAcc=0
netIOAcc=0

for resultFile in $output_dir/*; do
  nFiles=$(echo $nFiles + 1 | bc)
  x=$(grep 'Total:' $resultFile | awk '{print $5}')
  nRequests=$(echo "$nRequests" + "$x" | bc)
  x=$(grep 'Total:' $resultFile | awk '{print $7}')
  nReplies=$(echo "$nReplies + $x" | bc)
  x=$(grep 'Reply rate' $resultFile | awk '{print $7}')
  replyRateAcc=$(echo "$replyRateAcc + $x" | bc)
  x=$(grep 'Reply time' $resultFile | awk '{print $5}')
  replyTimeAcc=$(echo "$replyTimeAcc + $x" | bc)
  x=$(grep 'Net I/O:' $resultFile | awk '{print $3}')
  netIOAcc=$(echo "$netIOAcc + $x" | bc)
done

echo "Requests: $nRequests"
echo Replies: $nReplies
echo Reply rate: $(echo "scale=2; $replyRateAcc / $nFiles" | bc)
echo Reply time: $(echo "scale=2; $replyTimeAcc / $nFiles" | bc)
echo Net I/O: $netIOAcc

