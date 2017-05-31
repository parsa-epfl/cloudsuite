# @authors: Somya Arora, Arash Pourhabibi
#!/bin/bash
# Figure out the modified files in this push command or pull request
modified_files=$(git --no-pager diff --name-only ${TRAVIS_COMMIT_RANGE})
# Get benchmark name and tag name for this build
benchmark_name=${DH_REPO#*/}
tag_name=$IMG_TAG
if [ -z "$modified_files" ]
then
    echo "No Modifications required."
else
    echo "Checking against modified files"
fi
# Find out whether the files related with the current build were modified or not 
if ( grep -q "$benchmark_name/$tag_name" <<<$modified_files ) ||
   ( grep -q "$benchmark_name" <<<$modified_files && [ $tag_name = "latest" ] ) ||
   ( grep -q "travis.sh" <<<$modified_files ) ||
   ( grep -q ".travis.yml" <<<$modified_files )
then
    # if modified, then rebuild their docker image
    if [[ -z $ARM ]]
    then
        travis_wait 40 docker build -t $DH_REPO:$IMG_TAG $DF_PATH
    else # Build for aarch64
        docker run --rm --privileged multiarch/qemu-user-static:register
        travis_wait 40 docker build -t $DH_REPO:$IMG_TAG-aarch64 -f $DF_PATH/Dockerfile-aarch64 $DF_PATH
    fi
    
    #make sure build was successful
    result=$?
    if [ $result != "0" ]
    then
        return 1
    fi
    # Push if this file was triggerred by a push command (not a pull request)
    if [ "${TRAVIS_PULL_REQUEST}" = "false" ] && [ "${TRAVIS_BRANCH}" = "master" ]
    then
        docker login -e="$DOCKER_EMAIL" -u="$DOCKER_USER" -p="$DOCKER_PASS"
        # Pushing needs login, test if login was successful
        result=$?
        if [ $result != "0" ]
        then
            return 1
        fi
        # Push if it logged in, test if push was successful
  	    travis_wait 40 docker push $DH_REPO
        result=$?
        if [ $result != "0" ]
        then
            return 1
        fi
    else
        echo "No push command executed"
  	fi
# if no file related to this image was modified
else
    echo "No Modifications to this image"
fi
