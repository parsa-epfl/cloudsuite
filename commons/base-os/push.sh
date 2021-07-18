docker pull cloudsuitetest/debian:amd64 
docker pull cloudsuitetest/debian:arm64 
docker pull cloudsuitetest/debian:riscv64
docker manifest create --amend cloudsuitetest/debian:base-os cloudsuitetest/debian:amd64 cloudsuitetest/debian:arm64 cloudsuitetest/debian:riscv64
docker manifest push cloudsuitetest/debian:base-os
