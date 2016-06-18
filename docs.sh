#!/bin/bash
set -x
if grep -q "docs/" <<<$MODIFIED_FILES
then
  mkdir out;
  cd out
  git clone https://github.com/srora/cloudsuite.git
  cd cloudsuite
  #git remote add upstream https://github.com/srora/cloudsuite.git
  #git fetch upstream
  #git checkout -b gh-pages --track upstream/gh-pages
  git checkout gh-pages
  git config user.name ${GIT_USER}
  git config user.email ${GIT_EMAIL}
  git config credential.helper "store --file=.git/credentials"
  git config --global push.default matching
  echo "https://$GH_TOKEN:x-oauth-basic@github.com" >> .git/credentials
  #rm -rf docs
  #cp -R ../../docs ./docs
  #git add .
  #git commit -a -m "Rebuilding"
  git merge master --no-edit
  git push -f origin gh-pages
  cd ${TRAVIS_BUILD_DIR}
  rm -rf out
  git checkout master
  i=0
  mod_arr=($MODIFIED_FILES)
  rm -rf ./SpellCheck.txt
  while [[ ! -z ${mod_arr[i]} ]]; do
    echo "${mod_arr[i]} :- " > ./result.txt;
    <${mod_arr[i]} aspell pipe list -d en_US --encoding utf-8 --personal=./.aspell.en.pws |
    grep '[a-zA-Z]\+ [0-9]\+ [0-9]\+' -oh |
    grep '[a-zA-Z]\+' -o | sort | uniq |
    while read word; do
      grep -on "\<$word\>" ${mod_arr[i]};
    done >>./result.txt;
    sort -n ./result.txt -o ./result.txt;
    cat ./result.txt >> ./SpellCheck.txt;
    let i=i+1;
  done
  if [[ -s ./SpellCheck.txt ]]
  then
    echo "~~~  Spelling Errors  ~~~ "
    less ./SpellCheck.txt
    return 1;
  fi
  rm -rf ./result.txt
  rm -rf ./SpellCheck.txt
fi
