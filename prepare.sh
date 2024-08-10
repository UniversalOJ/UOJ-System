#!/bin/bash

echo "==> Preparing config file"

genRandStr(){
	cat /dev/urandom | tr -dc [:alnum:] | head -c $1
}

# 复制配置文件
cp web/app/.default-config.php .config.local.php
echo "==> Copied default config file"

# 替换配置文件中的 salt
sed -i -e "s/salt0/$(genRandStr 32)/g" \
	-e "s/salt1/$(genRandStr 16)/g" \
	-e "s/salt2/$(genRandStr 16)/g" \
	-e "s/salt3/$(genRandStr 16)/g" \
	.config.local.php
echo "==> Replaced salt in config file"

# 替换配置文件中的 _httpHost_
sed -i -e "s/'_httpHost_'/UOJContext::httpHost()/g" .config.local.php
echo "==> Replaced _httpHost_ in config file"

echo ""
echo "==> Done, config file is '.config.local.php'"
echo "==> Please modify the config file according to your needs"
echo "==> Then run 'docker-compose up -d' to start the service"
