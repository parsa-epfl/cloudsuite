set -x
echo "TRAVIS_COMMIT_RANGE= ${TRAVIS_COMMIT_RANGE}"
path=$(git --no-pager diff --name-only ${TRAVIS_COMMIT_RANGE})
paths=( $path )
counter=0
check1=${DH_REPO#*/}
check2=$IMG_TAG

echo $path
echo ${paths[counter]}

if [ -z "$path" ]
   then
	echo "No Modifications to this image"
fi

while [[ ${paths[counter]} ]];
 do
 	benchmark="${paths[counter]#*/}";
	tag="${benchmark#*/}";
	benchmark="${benchmark%%/*}";
	tag="${tag%%/*}";

	echo "Entered while"

	if [ "${check1}" = "${benchmark}" ] && [ "${check2}" = "${tag}" ]
	    then

		 travis_wait 40 docker build -t $DH_REPO:$IMG_TAG $DF_PATH

		 if [ "${TRAVIS_PULL_REQUEST}" = "false" ] && [ "${TRAVIS_BRANCH}" = "master" ]

		   then
			docker login -e="$DOCKER_EMAIL" -u="$DOCKER_USER" -p="$DOCKER_PASS"
			travis_wait 40 docker push $DH_REPO

		fi
	else
			echo "No Modifications to this image"
	fi
	let counter=counter+1;
done
