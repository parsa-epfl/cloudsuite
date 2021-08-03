#!/bin/bash

cat >> conftest.py <<EOF
import sys
print(sys.abiflags)

EOF

$1 conftest.py
EXIT_CODE=$?
rm -rf conftest.py
exit $EXIT_CODE
