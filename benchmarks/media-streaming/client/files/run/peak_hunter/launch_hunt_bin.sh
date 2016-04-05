#!/bin/bash

videoServerIp="$1"
hostFileName="$2"  
remoteOutputPath="$3"
numClientsPerHost="$4"

totalMinNumSessions="$5"
totalMaxNumSessions="$6"

if [ $# -ne 6 ]; then
  echo "Usage: launch_hunt_bin.sh <video_server_ip> <host_list_file> <remote_output_path> <num_clients_per_host> <min_num_sessions> <max_num_sessions>"
  exit 
fi

# Distribute the load
numHosts=$(wc -l < $hostFileName)
numTotalClients=$[$numHosts*$numClientsPerHost]
minNumSessions=$[$totalMinNumSessions/$numTotalClients]
maxNumSessions=$[$totalMaxNumSessions/$numTotalClients]

echo "Total clients = $numTotalClients"
echo "Minimum number of sessions = $minNumSessions"
echo "Maximum number of sessions = $maxNumSessions"

benchmarkSuccess=1

outputDir="/output"
backUpStdoutDir="/output-stdout"

rm -rf "$outputDir/*" "$backUpStdoutDir"
mkdir -p "$outputDir" "$backUpStdoutDir"

# Launches remote with the specified number of sessions. 
# Sets benchmarkSuccess to 1 or 0 depending on success/failure
function launchRemote () {
  totalConns=0
  totalErrors=0
  
  numSessions="$1"
  rate=$[numSessions/10]
  $(dirname $0)/launch_remote.sh $videoServerIp $hostFileName $remoteOutputPath $numClientsPerHost $numSessions $rate
  if [ $? -ne 0 ]; then
    echo 'Failed launching remote... exiting.'
    exit
  fi
  # Open each file in output directory
  for outputFile in $outputDir/*;
  do
    numConns="$(grep 'Total: connections' $outputFile | awk '{print $3}')"
    numErrors="$(grep 'Errors: total' $outputFile | awk '{print $3}')"
    totalConns=$[totalConns+numConns]
    totalErrors=$[totalErrors+numErrors]
  done
  percFailure=$[$totalErrors*100/$totalConns]
  echo "Total connections = $totalConns"
  echo "Total errors = $totalErrors"
  echo "Percentage failure = $percFailure"
  if [ "$percFailure" -gt 5 ]; then
    cp $backUpStdoutDir/* $outputDir
    sleep 10
    benchmarkSuccess=0
  else
    cp $outputDir/* $backUpStdoutDir
    benchmarkSuccess=1
  fi
}

# Test for minNumSessions
launchRemote $minNumSessions

if [ $benchmarkSuccess -eq 0 ]
then
  echo "Benchmark failed for $minNumSessions sessions"
  echo "Minimum Limit for number of sessions too high."
  exit 0
else
  echo "Benchmark succeeded for $minNumSessions sessions"
fi


# Test for maxNumSessions
launchRemote $maxNumSessions

if [ $benchmarkSuccess -eq 1 ]
then
  echo "Benchmark succeeded for $maxNumSessions sessions"
  echo "Maximum limit for number of sessions too low."
  exit 0
else
  echo "Benchmark failed for $maxNumSessions sessions"
fi

lowLimSessions=$minNumSessions
hiLimSessions=$maxNumSessions

# Launch binary search
while :
do
  diff=$[maxNumSessions-minNumSessions]
  if [ $diff -le 50 ]
  then
    maxThroughput=$[$numSessions*$numTotalClients]
#    echo "Benchmark succeeded for maximum sessions: $maxThroughput"
    exit 0
  fi
  delta=$[(maxNumSessions-minNumSessions)/2]
  numSessions=$[minNumSessions+delta]
  launchRemote $numSessions
  if [ "$benchmarkSuccess" -eq 0 ]
  then
    maxNumSessions=$numSessions
  else
    minNumSessions=$numSessions
  fi
done
