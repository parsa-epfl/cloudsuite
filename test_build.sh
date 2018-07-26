set -e

export DH_REPO="cloudsuite/memcached" 
export IMG_TAG="1.5.9" 
export DF_PATH="./commons/memcached/1.5.9"
docker build -t $DH_REPO:$IMG_TAG $DF_PATH

export DH_REPO="cloudsuite/data-caching"
export IMG_TAG="server" 
export DF_PATH="./benchmarks/data-caching/server"
docker build -t $DH_REPO:$IMG_TAG $DF_PATH

export DH_REPO="cloudsuite/data-caching" 
export IMG_TAG="client" 
export DF_PATH="./benchmarks/data-caching/client"
docker build -t $DH_REPO:$IMG_TAG $DF_PATH

export DH_REPO="cloudsuite/memcached_server" 
export IMG_TAG="latest" 
export DF_PATH="./benchmarks/web-serving/memcached_server"
docker build -t $DH_REPO:$IMG_TAG $DF_PATH

