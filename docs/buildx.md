# Docker Buildx
Docker Buildx is a CLI plugin that extends the docker command with the full support of the features provided by Moby BuildKit builder toolkit. BuildKit is designed to work well for building for multiple platforms and not only for the architecture and operating system that the user invoking the build happens to run. More information can be found [docker-buildx](https://docs.docker.com/buildx/working-with-buildx/)

### Enable docker experimental client flag
```
mkdir -p $HOME/.docker \
    && echo "{\"experimental\": \"enabled\"}" > $HOME/.docker/config.json \
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
Delete existing instance, if it exists
```
docker buildx rm host-builder
```
Create a new builder instance with name host-builder
```
docker buildx create --use --name host-builder --buildkitd-flags '--allow-insecure-entitlement network.host' \
    && docker buildx use host-builder \
    && docker buildx inspect --bootstrap
```

For more information check [docker-github](https://github.com/docker/buildx/blob/master/README.md)

### Using buildx
Docker login to enable push of images onto [dockerhub](https://hub.docker.com/u/cloudsuite/). 
```
docker login
```

#### Creating the base-os image
Run `build.sh` script in `cloudsuite/commons/base-os` to generate base images for different architectures in one manifest
