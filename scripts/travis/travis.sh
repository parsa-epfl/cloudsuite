# @authors: Somya Arora, Arash Pourhabibi
#!/bin/bash
# Figure out the modified files in this push command or pull request
modified_files=$(git --no-pager diff --name-only ${TRAVIS_COMMIT_RANGE})
# Get benchmark name and tag name for this build
benchmark_name=${DH_REPO#*/}
tag_name=$IMG_TAG
if [ -z "$modified_files" ]; then
    echo "No Modifications required."
else
    echo "Checking against modified files"
fi
# Find out whether the files related with the current build were modified or not
if (grep -q "$benchmark_name/$tag_name" <<<$modified_files) ||
    (grep -q "$benchmark_name" <<<$modified_files && [ $tag_name = "latest" ]) ||
    (grep -q "travis.sh" <<<$modified_files) ||
    (grep -q ".travis.yml" <<<$modified_files); then
    # Push if this file was triggerred by a push command (not a pull request)
    docker login -u="$DOCKER_USER" -p="$DOCKER_PASS"
    # Pushing needs login, test if login was successful
    result=$?
    if [ $result != "0" ]; then
        return 1
    else
        echo "Docker login succeeded"
    fi
    # if modified, then rebuild annd push their docker image
    travis_wait 40 docker buildx build --platform linux/amd64,linux/arm64,linux/riscv64 -t $DH_REPO:$IMG_TAG --push $DF_PATH
    #make sure build was successful
    result=$?
    if [ $result != "0" ]; then
        return 1
    fi
# if no file related to this image was modified
else
    echo "No Modifications to this image"
fi
