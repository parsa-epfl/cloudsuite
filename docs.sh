#!/bin/bash
set -x
MODIFIED_FILES=$(git --no-pager diff --name-only ${TRAVIS_COMMIT_RANGE})
if grep -q "docs/" <<<$MODIFIED_FILES
then
  mkdir out;
  cd out
  git clone https://github.com/srora/cloudsuite.git
  cd cloudsuite
  git checkout gh-pages
  git config user.name ${GIT_USER}
  git config user.email ${GIT_EMAIL}
  git config credential.helper "store --file=.git/credentials"
  git config --global push.default matching
  echo "https://$GH_TOKEN:x-oauth-basic@github.com" >> .git/credentials
  git merge master --no-edit
  result=$?
  if [ $result -eq "1" ]
  then
      return 1
  fi
  git push -f origin gh-pages
  result=$?
  if [ $result -eq "1" ]
  then
      return 1
  fi
  cd ${TRAVIS_BUILD_DIR}
  rm -rf out
  git checkout master
  sudo apt-get install -y aspell-en
  modified_files_arr=($MODIFIED_FILES)
  rm -rf ./Misspelled_words.txt
  for docs_file_modified in "${modified_files_arr[@]}"
  do
    if grep -q "docs/" <<<$docs_file_modified
    then
      echo "$docs_file_modified :- " > ./misspelled_per_file.txt;
      <$docs_file_modified aspell pipe list -d en_US --encoding utf-8 --personal=./.aspell.en.pws |
      grep '[a-zA-Z]\+ [0-9]\+ [0-9]\+' -oh |
      grep '[a-zA-Z]\+' -o | sort | uniq |
      while read word; do
        grep -on "\<$word\>" $docs_file_modified;
      done >>./misspelled_per_file.txt;
      sort -n ./misspelled_per_file.txt -o ./misspelled_per_file.txt;
      cat ./misspelled_per_file.txt >> ./Misspelled_words.txt;
    fi
  done
  if [[ -s ./Misspelled_words.txt ]]
  then
    echo "~~~  Spelling Errors  ~~~ "
    cat ./Misspelled_words.txt
    return 1;
  fi
  rm -rf ./misspelled_word_per_file.txt
  rm -rf ./Misspelled_words.txt
fi
