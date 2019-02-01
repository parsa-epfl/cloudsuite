# @authors: Somya Arora, Arash Pourhabibi
#!/bin/bash
# Figure out the modified files in this push command or pull request
MODIFIED_FILES=$(git --no-pager diff --name-only ${TRAVIS_COMMIT_RANGE})
if grep -q "docs/" <<<$MODIFIED_FILES
then
  echo "Modified doc files found"
  # If .md files have been modified, we run a spellcheck
  # Install aspell (tool used for Spellcheck)
  sudo apt update
  sudo apt install -y aspell-en
  # Test if installation was successful
  result=$?
  if [ $result -eq "0" ]
  then
    echo "Successfully installed aspell-en"
    modified_files_arr=($MODIFIED_FILES)
    # for each file modified
    for docs_file_modified in "${modified_files_arr[@]}"
    do
      # if this modified file is a doc file
      if grep -q "docs/" <<<$docs_file_modified
      then
        # Run spell check using aspell and find out file name and line no. of each misspelled words, sorting them acc. to line no.
        # Ignore certain selected words -  listed in /tests/.aspell.en.pws
        <$docs_file_modified aspell pipe list -d en_US --encoding utf-8 --personal=./scripts/travis/.aspell.en.pws |
        grep '[a-zA-Z]\+ [0-9]\+ [0-9]\+' -oh |
        grep '[a-zA-Z]\+' -o | sort | uniq |
        while read word; do
          grep -on "\<$word\>" $docs_file_modified;
        done >>./misspelled_per_file.txt;
        if [[ -s ./misspelled_per_file.txt ]]
        then
          sort -n ./misspelled_per_file.txt -o ./misspelled_per_file.txt;
          echo "Misspelled words in File : $docs_file_modified :- " > ./Misspelled_words.txt;
          cat ./misspelled_per_file.txt >> ./Misspelled_words.txt;
        fi
      fi
    done
    # Display misspelled words, if found in any .md file modified
    if [[ -s ./Misspelled_words.txt ]]
    then
      echo "~~~  Spelling Errors  ~~~ "
      cat ./Misspelled_words.txt
      # Don't build successfully in case of misspellings
      return 1;
    else
      echo "Spell Check Successful : No erros found."
    fi
    # If travis was triggered by a push in master
    if [ "${TRAVIS_PULL_REQUEST}" = "false" ] && [ "${TRAVIS_BRANCH}" = "master" ]
    then
      mkdir out;
      cd out
      git clone https://github.com/parsa-epfl/cloudsuite.git
      cd cloudsuite
      git checkout gh-pages
      # Credentials to allow a merge and push to auto-update documentation website through gh-pages branch 
      git config user.name ${GIT_USER}
      git config user.email ${GIT_EMAIL}
      git config credential.helper "store --file=.git/credentials"
      git config --global push.default matching
      echo "https://$GH_TOKEN:x-oauth-basic@github.com" >> .git/credentials
      git merge master --no-edit
      # Test if merge was successful
      result=$?
      if [ $result != "0" ]
      then
        echo "Merge Failed"
        return 1
      else
        echo "Merge Successful"
        # Update gh-pages branch
        git push -f origin gh-pages
        result=$?
        if [ $result == "0" ]
        then
          echo "Successfully updated branch gh-pages"
        else
          echo "Push command Failed"
          return 1
        fi
      fi
    fi
  else
    echo "Installation of Aspell Failed : No updates performed."
    return 1
  fi
else
  echo "No modifications to Doc files"
fi
