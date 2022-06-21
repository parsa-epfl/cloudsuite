#!/bin/bash
if [[ $# -eq 2 ]] ; then
    echo "$1 $2" >> /etc/hosts
fi

/usr/sbin/sshd -D
