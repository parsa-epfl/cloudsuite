docker pull cloudsuite/debian:amd64 
docker pull cloudsuite/debian:arm64 
docker pull cloudsuite/debian:riscv64
docker manifest create --amend cloudsuite/debian:base-os cloudsuite/debian:amd64 cloudsuite/debian:arm64 cloudsuite/debian:riscv64
docker manifest push cloudsuite/debian:base-os
