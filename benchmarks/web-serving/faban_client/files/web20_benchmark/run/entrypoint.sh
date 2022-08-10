#!/bin/bash


# parse args
ARGS=()
OPER="usergen&run"
TRPU=30
TRPD=30
TSTD=30
TMIN=100
TMAX=150
TYPE=THINKTIME
DIST=fixed

while (( ${#@} )); do
  case ${1} in
    --oper=*)      OPER=${1#*=} ;;
  --ramp-up=*)   TRPU=${1#*=} ;;
--ramp-down=*) TRPD=${1#*=} ;;
  --steady=*)    TSTD=${1#*=} ;;
--min=*)       TMIN=${1#*=} ;;
    --max=*)       TMAX=${1#*=} ;;
  --type=*)      TYPE=${1#*=} ;;
--dist=*)      DIST=${1#*=} ;;
    *)             ARGS+=(${1}) ;;
  esac

  shift
done

set -- ${ARGS[@]}


if [[ ${#} -lt 1 ]]; then
  echo "usage: web_server_ip [scale] [options]"
  exit 1
fi

WSIP=${1}
THRD=${2:-1}

boot() {
  while [[ $(curl -sSI ${WSIP}:8080 | awk '/HTTP\/1.1/{print $2}') != 200 ]]; do
    echo "Could not perform HTTP 200 GET from: ${WSIP}:8080"
    sleep 2
  done

  sed -i -e "s/<host.*/<host>${WSIP}<\\/host>/" \
    deploy/run.xml

  ${FABAN_HOME}/master/bin/startup.sh

}

init() {
  local thrd=$((${THRD} * 1))

  sed -i -e "s/num_users=.*/num_users=${thrd}/" \
    ${FABAN_HOME}/usersetup.properties

  ant usergen-jar
  ant usergen-run -Darg0=http://${WSIP}:8080
}

fini() {
  echo 'DONE'
  #   exec tail -f /dev/null
  exit
}

gen() {
  mkdir -p ${1}

  sed -i -e "s/<fa:scale>.*/<fa:scale>${THRD}<\\/fa:scale>/"                  \
    -e "s/<fa:rampUp.*/<fa:rampUp>${TRPU}<\\/fa:rampUp>/"                \
    -e "s/<fa:rampDown.*/<fa:rampDown>${TRPD}<\\/fa:rampDown>/"          \
    -e "s/<fa:steadyState.*/<fa:steadyState>${TSTD}<\\/fa:steadyState>/" \
    -e "s/<host.*/<host>${WSIP}<\\/host>/" \
    -e "s/<port.*/<port>8080<\\/port>/" \
    -e "s@<outputDir.*@<outputDir>${FABAN_HOME}\/output<\\/outputDir>@" /web20_benchmark/deploy/run.xml
    deploy/run.xml

  cat <<EOF > ${1}/cfg
  timing = ${DIST}, min = ${TMIN}, max = ${TMAX}, type = ${TYPE}
  background.time.0.type = ${TYPE}
EOF

  bin/gen -i src/workload/driver/Web20Driver.java.in \
    -o src/workload/driver/Web20Driver.java    \
    ${1}/cfg

  cp  deploy/run.xml                       ${1}
  cp  src/workload/driver/Web20Driver.java ${1}

  ant deploy.jar
  cp  build/Web20Driver.jar ${FABAN_HOME}/benchmarks
}

run() {
  [[ -z ${TMIN} ]] && TMIN=${args[0]}
  [[ -z ${TMAX} ]] && TMAX=${args[1]}
  [[ -z ${TYPE} ]] && TYPE=${args[3]}
  [[ -z ${DIST} ]] && DIST=${args[4]}

  if [[ -z ${THRD} || \
    -z ${TMIN} || \
    -z ${TMAX} || \
    -z ${TYPE} || \
    -z ${DIST} ]]; then
  echo "ERROR: not all the parameters specified"
  exit
fi

echo "--------------------------------"
echo "scale:      ${THRD}"
echo "ramp up:    ${TRPU}"
echo "steady:     ${TSTD}"
echo "ramp down:  ${TRPD}"
echo "delay dist: ${DIST}"
echo "delay type: ${TYPE}"
echo "delay min:  ${TMIN}"
echo "delay max:  ${TMAX}"
echo "--------------------------------"
sleep 1

local dir="TH_${THRD}-TM_${TMIN}-TY_${TYPE}-DS_${DIST}"

gen output/${dir}
ant run

cp -r $(ls -td ${FABAN_HOME}/output/*/ | head -1) output/${dir}

unset -v latest
for file in "output/$dir"/*/summary.xml; do
  [[ $file -nt $latest ]] && latest=$file
done

echo "--------------------------------"
echo "RESULT: ${latest}"
echo "--------------------------------"
cat ${latest}
echo "--------------------------------"
}


boot

echo "OPER is ${OPER}"

if [ ${OPER} = "usergen" ] 
then
  echo "Generating Users"
  init
  fini
fi

if [ ${OPER} = "usergen&run" ] 
then
  echo "Generating Users and Running the benchmark"
  init
  run
  fini
fi

if [ ${OPER} = "run" ]
then
  echo "Running the benchmark"
  run
  fini
fi
