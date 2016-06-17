#!/bin/bash
set -x
modified_files=$(git --no-pager diff --name-only ${TRAVIS_COMMIT_RANGE})
if grep -q "docs/" <<<$modified_files
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
  git merge master
  git push -f origin gh-pages
  cd ${TRAVIS_BUILD_DIR}
  rm -rf out
  git checkout master
fi
