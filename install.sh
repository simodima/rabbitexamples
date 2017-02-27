#!/usr/bin/env bash

docker-compose -f docker-compose.install.yml build
docker-compose -f docker-compose.install.yml up -d
docker-compose -f docker-compose.install.yml run installer logger
docker-compose -f docker-compose.install.yml run installer application
docker-compose -f docker-compose.install.yml run installer downloader

docker-compose up -d
docker-compose run remote_api npm install

