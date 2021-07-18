docker pull cloudsuitetest/debian:amd64 
docker manifest create --amend cloudsuitetest/debian:base-os cloudsuitetest/debian:amd64
docker manifest push cloudsuitetest/debian:base-os
