# Running Buildx

Docker Buildx is a CLI plugin that extends the docker command with the full support of the features provided by Moby BuildKit builder toolkit. BuildKit is designed to work well for building for multiple platforms and not only for the architecture and operating system that the user invoking the build happens to run. More information can be found [docker-buildx](https://docs.docker.com/buildx/working-with-buildx/)

### Enable docker experimental client flag
```
mkdir -p ~/.docker \
    && echo "{\"experimental\": \"enabled\"}" >~/.docker/config.json \
    && sudo service docker restart
```

On linux, `docker version` should show `Experimental: true` for Client Docker Engine

For more information check [docker-arm](https://www.docker.com/blog/getting-started-with-docker-for-arm-on-linux/)

### Enable docker experimental server flag
```
sudo mkdir -p /etc/systemd/system/docker.service.d && \
	echo -e "[Service]\nExecStart=\nExecStart=/usr/bin/dockerd --experimental" | sudo tee -a /etc/systemd/system/docker.service.d/override.conf && \
	sudo systemctl daemon-reload && \
	sudo systemctl restart docker.service
```
On linux, `docker version` should show `Experimental: true` for Server Docker Engine

### Enable execution of different multi-architecture QEMU
```
docker run --rm --privileged multiarch/qemu-user-static --reset -p yes
```

For more information check [qemu-user-static](https://github.com/multiarch/qemu-user-static)

### Create a build instance
```
docker buildx rm host-builder \
    && docker buildx create --use --name host-builder --buildkitd-flags '--allow-insecure-entitlement network.host' \
    && docker buildx use host-builder \
    && docker buildx inspect --bootstrap
```

This creates a builder instance with name host-builder

For more information check [docker-github](https://github.com/docker/buildx/blob/master/README.md)

### Using buildx
Docker login to enable push of images onto [dockerhub](https://hub.docker.com/u/cloudsuite/). 

#### Creating the base-os image
```
docker buildx build --platform=linux/arm64,linux/amd64,linux/riscv64 --tag=cloudsuite/debian:base-os --progress=plain --network=host --push .
```
