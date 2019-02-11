#!/bin/bash

if [ "$1" == "--help" ]
then
    echo "Usage: [UWSGI_ONLY=1] ./build_containers [/absolute/path/to/cpython/install]"
    exit 0
fi


# default Python build
if [[ $# -lt 1 ]]
then
    cp uwsgi/Dockerfile.default uwsgi/Dockerfile
    cd uwsgi/
    sudo docker build --no-cache -t uwsgi-webtier .
    cd ../
# custom Python build
else
    rm -rf uwsgi/cpython
    cp -r $1 uwsgi/cpython
    PLATFORM_TRIPLET=$(./get_platform_triplet.sh)
    if [ $? -ne 0 ]; then exit $?; fi 
    PYTHON_SOABI=$(./get_python_soabi.sh uwsgi/cpython/bin/python3)
    if [ $? -ne 0 ]; then exit $?; fi 
    PYTHON_VERSION=$(./get_python_version.sh uwsgi/cpython/bin/python3)
    if [ $? -ne 0 ]; then exit $?; fi 
    PYTHON_SHARED=$(./get_python_shared.sh uwsgi/cpython/bin/python3)
    if [ $? -ne 0 ]; then exit $?; fi 
    echo $PLATFORM_TRIPLET $PYTHON_SOABI $PYTHON_VERSION $PYTHON_SHARED

    if [[ $PYTHON_SHARED == "1" ]]
    then
        cp uwsgi/Dockerfile.shared uwsgi/Dockerfile
    else
        cp uwsgi/Dockerfile.static uwsgi/Dockerfile
    fi
    cd uwsgi/
    echo "Building image for uwsgi-webtier"
    sudo docker build --no-cache -t uwsgi-webtier                        \
                 --build-arg cpython_install="cpython"              \
                 --build-arg platform_triplet="$PLATFORM_TRIPLET"   \
                 --build-arg python_soabi="$PYTHON_SOABI"           \
                 --build-arg python_version="$PYTHON_VERSION" .
    echo "-------------------------------------------------------------"
    echo
    cd ../
fi

if [[ "$UWSGI_ONLY" != "1" ]]
then
    DOCKER_LOCATIONS="cassandra graphite memcached siege"
    for d in $DOCKER_LOCATIONS
    do
        cd "$d"
        echo "Building image for $d-webtier"
        sudo docker build --no-cache -t "$d"-webtier .
        echo "-------------------------------------------------------------"
        echo
        cd ../ 
    done
fi
