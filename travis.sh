path=$(git --no-pager diff --name-only ${TRAVIS_COMMIT_RANGE})
should_be_built=0
benchmark_name=${DH_REPO#*/}
tag_name=$IMG_TAG

if [ -z "$path" ]
   then
	echo "No Modifications required."
else
  echo "Checking against modified files"
fi

if grep -q "$benchmark_name/$tag_name" <<<$path || grep -q "$benchmark_name/latest" <<<$path
   then

  travis_wait 40 docker build -t $DH_REPO:$IMG_TAG $DF_PATH
  should_be_built=1

  		 if [ "${TRAVIS_PULL_REQUEST}" = "false" ] && [ "${TRAVIS_BRANCH}" = "master" ]
  		   then

        docker login -e="$DOCKER_EMAIL" -u="$DOCKER_USER" -p="$DOCKER_PASS"
  			travis_wait 40 docker push $DH_REPO

      else
        echo "No push command executed"

  		fi

    fi

  if [ $should_be_built -eq 0 ]
     then
    echo "No Modifications to this image"
  fi
