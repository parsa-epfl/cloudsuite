docker pull cloudsuitetest/java:openjdk11_amd64 
docker manifest create --amend cloudsuitetest/java:openjdk11 cloudsuitetest/java:openjdk11_amd64
docker manifest push cloudsuitetest/java:openjdk11

