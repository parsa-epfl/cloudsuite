#!/bin/bash

cat >> conftest.py <<EOF
from distutils import sysconfig
print(sysconfig.get_config_var('VERSION'))

EOF

$1 conftest.py
EXIT_CODE=$?
rm -rf conftest.py
exit $EXIT_CODE
