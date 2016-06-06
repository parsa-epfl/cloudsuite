path=$(git --no-pager diff --name-only ${TRAVIS_BRANCH} $(git merge-base ${TRAVIS_BRANCH} master))
paths=( $path )
counter=0
check1=”${DH_REPO#*/}”
check2=$IMG_TAG

while [[ ${paths[counter]} ]]; 
 do 
 	benchmark="${paths[counter]#*/}"; 
	tag="${benchmark#*/}"; 
	benchmark="${benchmark%%/*}"; 
	tag="${tag%%/*}"; 

	if [ “${check1}” -eq “${benchmark} && “${check2}” -eq “${tag}” ]
	    then
		
		 travis_wait 40 docker build -t $DH_REPO:$IMG_TAG $DF_PATH
		
		 if [ “${TRAVIS_PULL_REQUEST}” -eq “false” && “${TRAVIS_BRANCH}” -eq “master” ]
		 
		   then
			travis_wait 40 docker push $DH_REPO
		fi
	fi
	let counter=counter+1; 
done
