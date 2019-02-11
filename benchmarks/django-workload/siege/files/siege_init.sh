#!/bin/bash

rm -rf /tmp/siege*
chown -R tester:tester /tmp

echo "Running with $SIEGE_WORKERS Siege workers"
su - tester -c "cd /home/tester/django-workload/client                     \
                && sed -i 's/localhost/$TARGET_ENDPOINT/g' urls.txt        \
                && LOG='/tmp/siege.log' WORKERS=$SIEGE_WORKERS ./run-siege"

