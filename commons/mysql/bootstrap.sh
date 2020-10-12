#!/bin/bash


# workaround for overlayfs:
# https://docs.docker.com/engine/userguide/storagedriver/overlayfs-driver/#limitations-on-overlayfs-compatibility
find /var/lib/mysql -type f -exec touch {} \;

chmod a+x /execute.sh
sync

bash -c "/execute.sh root" #root is the root pass
