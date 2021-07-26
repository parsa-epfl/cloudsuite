#!/bin/bash

# @authors: Somya Arora, Arash Pourhabibi
# @modified: Shanqing Lin

# 1. Figure out the modified files
if [ "${GITHUB_EVENT_NAME}" = "pull_request" ]; then
  modified_files=$(git --no-pager diff --name-only ${PR_COMMIT_RANGE})
else
  modified_files=$(git --no-pager diff --name-only ${PUSH_COMMIT_RANGE})
fi
# 2.Get benchmark name and tag name for this build
image_name=${DH_REPO#*/}
tag_name=$IMG_TAG
if [ -z "$modified_files" ]; then
    echo "No Modifications required."
else
    echo "Checking against modified files"
fi
# 3.Find out whether the files related with the current build were modified or not
if (grep -q "${DF_PATH#./}" <<<$modified_files) || # Rebuild the image if any file in the build folder is changed 
    (grep -q "build-images.sh" <<<$modified_files) ||
    (grep -q "build-images.yaml" <<<$modified_files); then

    # if modified, then rebuild their docker image

    # install QEMU for extra arch
    arch_list=${DBX_PLATFORM//linux\//} # linux/amd64,linux/arm64,linux/riscv64 -> amd64,arm64,riscv64
    echo "Platforms: ${arch_list}"
    extra_arch_list=${${$arch_list#amd64}#,} # amd64,arm64,riscv64 -> arm64,riscv64
    # reference: https://github.com/tonistiigi/binfmt/
    if [ $extra_arch_list ]; then
        docker run --rm --privileged 'tonistiigi/binfmt:latest' --install $extra_arch_list
    else
        echo "No extra arch is found, skipping install QEMU."
    fi
    # remove build cache
    docker buildx prune -a -f
    # reference: https://github.com/docker/buildx/issues/495
    docker run --rm --privileged multiarch/qemu-user-static --reset -p yes
    docker buildx create --name multiarch --driver docker-container --use
    docker buildx inspect --bootstrap

    if [ $image_name = "debian" ]; then
        cd commons/base-os
        for arch in amd64 arm64 riscv64; do 
            docker buildx build --platform=linux/${arch} -t $DH_REPO:${arch} -f Dockerfile.${arch} .
            if [ $? != "0" ]; then
                exit 1
            fi
        done
        docker manifest create --amend $DH_REPO:base-os $DH_REPO:amd64 $DH_REPO:arm64 $DH_REPO:riscv64
    else
        docker buildx build --platform $DBX_PLATFORM -t $DH_REPO:$IMG_TAG $DF_PATH
    fi

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

        if [ $image_name = "debian" ]; then
            docker manifest push $DH_REPO:base-os
        else
            # Push the docker image
            docker buildx build --platform $DBX_PLATFORM -t $DH_REPO:$IMG_TAG --push $DF_PATH
        fi
        
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
