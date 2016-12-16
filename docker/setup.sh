#!/bin/bash

set -e

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

docker build -t validoo_image $DIR

docker rm -f validoo_container || echo "Previous container not found"
docker run -d -v $DIR/../:/home/validoo/project --name validoo_container -ti validoo_image bash

sleep 1

docker exec -ti -u validoo validoo_container zsh
