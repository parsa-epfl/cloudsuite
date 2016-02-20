Image for local testing of PerfKit.

It contains sshd as required by PerfKit, and docker so that we can start docker
containers within docker containers.

Build with the provided build.sh script, because it generates ssh keys before
building the image.

Start containers with --privileged to be able to run docker within docker.

