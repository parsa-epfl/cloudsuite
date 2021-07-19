docker pull cloudsuite/java:openjdk11_amd64 
docker pull cloudsuite/java:openjdk11_arm64 
docker pull cloudsuite/java:openjdk11_riscv64

docker manifest create --amend cloudsuite/java:openjdk11 cloudsuite/java:openjdk11_amd64 cloudsuite/java:openjdk11_arm64 cloudsuite/java:openjdk11_riscv64
docker manifest push cloudsuite/java:openjdk11

