docker pull cloudsuitetest/java:openjdk11_amd64 
docker pull cloudsuitetest/java:openjdk11_arm64 
docker pull cloudsuitetest/java:openjdk11_riscv64

docker manifest create --amend cloudsuitetest/java:openjdk11 cloudsuitetest/java:openjdk11_amd64 cloudsuitetest/java:openjdk11_arm64 cloudsuitetest/java:openjdk11_riscv64
docker manifest push cloudsuitetest/java:openjdk11

