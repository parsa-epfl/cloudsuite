#!/bin/bash

# @authors: Somya Arora, Arash Pourhabibi
# @modified: Shanqing Lin

# TODO: Merge this script with the main script.

# 1. Figure out the modified files
if [ "${GITHUB_EVENT_NAME}" = "pull_request" ]; then
  modified_files=$(git --no-pager diff --name-only ${PR_COMMIT_RANGE})
else
  modified_files=$(git --no-pager diff --name-only ${PUSH_COMMIT_RANGE})
fi
# 2.Get benchmark name and tag name for this build
if [ -z "$modified_files" ]; then
    echo "No Modifications required."
else
    echo "Checking against modified files"
fi
# 3.Find out whether the files related with the current build were modified or not
if (grep -q "commons/base-os" <<<$modified_files) ||
    (grep -q "build-images-base-os.sh" <<<$modified_files) ||
    (grep -q "build-images.yaml" <<<$modified_files); then
    # if modified, then rebuild their docker image
    docker buildx prune -a -f
    docker run --rm --privileged multiarch/qemu-user-static --reset -p yes
    docker buildx create --name multiarch --driver docker-container --use
    docker buildx inspect --bootstrap
    
    cd commons/base-os
    for arch in amd64 arm64; do
        docker buildx build --platform=linux/${arch} -t cloudsuitetest/debian:${arch} -f Dockerfile.${arch} .
        if [ $? != "0" ]; then
            exit 1
        fi
    done
    docker manifest create --amend cloudsuitetest/debian:base-os cloudsuitetest/debian:amd64 cloudsuitetest/debian:arm64
    # make sure build was successful
    if [ $? != "0" ]; then
        exit 1
    fi
    # Push if this file was triggerred by a push command (not a pull request)
    if ( [ "${GITHUB_EVENT_NAME}" = "push" ] && [ "${GITHUB_REF}" = "refs/heads/master" ] ) || [ "${FORCE_PUSH}" = "true" ]; then
        docker login -u="$DOCKER_USER" -p="$DOCKER_PASS"
        # Pushing needs login, test if login was successful
        if [ $? != "0" ]; then
            exit 1
        fi
        # Push the docker image
        docker manifest push cloudsuitetest/debian:base-os
        if [ $? != "0" ]; then
            exit 1
        fi
    else
        echo "No push command executed"
    fi
# if no file related to this image was modified
else
    echo "No Modifications to this image"
fi
