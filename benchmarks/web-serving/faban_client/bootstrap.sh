#!/bin/bash

if [ $# -gt 2 ]; then
  echo "Usage: faban_client WEB_SERVER_IP [LOAD_SCALE]"
  exit 1
fi

if [ $# -lt 1 ]; then
  echo "Web server IP is a mandatory parameter."
  exit 1
fi

WEB_SERVER_IP=$1
LOAD_SCALE=${2:-7}

while [ "$(curl -sSI ${WEB_SERVER_IP}:8080 | grep 'HTTP/1.1' | awk '{print $2}')" != "200" ]; do
  sleep 1
done

/faban/master/bin/startup.sh
cd /web20_benchmark/build && java -jar Usergen.jar http://${WEB_SERVER_IP}:8080
sed -i "s/<fa:scale.*/<fa:scale>${LOAD_SCALE}<\\/fa:scale>/" /web20_benchmark/deploy/run.xml
sed -i "s/<fa:rampUp.*/<fa:rampUp>10<\\/fa:rampUp>/" /web20_benchmark/deploy/run.xml
sed -i "s/<fa:rampDown.*/<fa:rampDown>10<\\/fa:rampDown>/" /web20_benchmark/deploy/run.xml
sed -i "s/<fa:steadyState.*/<fa:steadyState>30<\\/fa:steadyState>/" /web20_benchmark/deploy/run.xml
sed -i "s/<host.*/<host>${WEB_SERVER_IP}<\\/host>/" /web20_benchmark/deploy/run.xml
sed -i "s/<port.*/<port>8080<\\/port>/" /web20_benchmark/deploy/run.xml
sed -i "s/<outputDir.*/<outputDir>\/faban\/output<\\/outputDir>/" /web20_benchmark/deploy/run.xml
cd /web20_benchmark && ant run
cat /faban/output/*/summary.xml

