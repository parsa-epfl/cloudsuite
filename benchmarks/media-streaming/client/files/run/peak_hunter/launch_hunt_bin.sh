#!/bin/bash

videoServerIp="$1"
hostFileName="$2"  
remoteOutputPath="$3"
numClientsPerHost="$4"
totalNumSessions="$5"
rate="$6"
mode="$7"

if [ $# -ne 7 ]; then
  echo "Usage: launch_hunt_bin.sh <video_server_ip> <host_list_file> <remote_output_path> <num_clients_per_host> <total_num_sessions> <rate> <encryption_mode>"
  exit 
fi

# Distribute the load
numHosts=$(wc -l < $hostFileName)
numTotalClients=$[$numHosts*$numClientsPerHost]
NumSessions=$[$totalNumSessions/$numTotalClients]
rate=`echo "scale=2; $rate/$numTotalClients" | bc`

echo "Total clients = $numTotalClients"
echo "Total number of sessions = $totalNumSessions"

outputDir="/output"
backUpStdoutDir="/output-stdout"

rm -rf "$outputDir/*" "$backUpStdoutDir"
mkdir -p "$outputDir" "$backUpStdoutDir"

# Launches remote with the specified number of sessions. 
function launchRemote () {
  totalConns=0
  totalErrors=0
  
  numSessions="$1"
  $(dirname $0)/launch_remote.sh $videoServerIp $hostFileName $remoteOutputPath $numClientsPerHost $numSessions $rate $mode
  if [ $? -ne 0 ]; then
    echo 'Failed launching remote... exiting.'
    exit
  fi
  # Open each file in output directory
  totalConns=0
  for outputFile in $outputDir/*;
  do
    numConns="$(grep 'Total: connections' $outputFile | awk '{print $3}')"
    numErrors="$(grep 'Errors: total' $outputFile | awk '{print $3}')"
    totalConns=$[totalConns+numConns]
    totalErrors=$[totalErrors+numErrors]
  done

  if [ $totalConns -eq 0 ]; then
    echo "No log is found from the log folder: $outputDir"
    echo "Please check the the folder exists, or whether any request is sent during the test"
  else
    percFailure=$[$totalErrors*100/$totalConns]
    echo "Total connections = $totalConns"
    echo "Total errors = $totalErrors"
    echo "Percentage failure = $percFailure"
    if [ "$percFailure" -gt 5 ]; then
      cp $backUpStdoutDir/* $outputDir
      sleep 10
    else
      cp $outputDir/* $backUpStdoutDir
    fi
  fi
}

# Test for NumSessions
launchRemote $NumSessions

exit 0
