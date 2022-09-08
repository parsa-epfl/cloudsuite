#!/bin/bash

IP=$1
SOLR_PORT=8983
until $(curl --output /dev/null --silent --head --fail http://$IP:${SOLR_PORT}); do
  printf '.'
  sleep 5
done

sleep 10

URL="http://$IP:${SOLR_PORT}/solr/cloudsuite_web_search/query?q=websearch&lang=en&fl=url&df=text&rows=10"

response=$(curl -s -w "%{http_code}" $URL)

http_code=$(tail -n1 <<< "$response")  # get the last line
content=$(sed '$ d' <<< "$response")   # get all but the last line which contains the status code

echo "$http_code"
echo "$content"
