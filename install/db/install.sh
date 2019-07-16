#!/bin/bash

setLAMPConf(){
    printf "\n\n==> Setting LAMP configs\n"
    #Set MySQL connection config
    cat >/etc/mysql/conf.d/uoj_mysqld.cnf <<UOJEOF
[mysqld]
default-time-zone='+8:00'
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
init_connect='SET NAMES utf8mb4'
init_connect='SET collation_connection = utf8mb4_unicode_ci'
skip-character-set-client-handshake
sql-mode=ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
UOJEOF
}

setWebConf(){
    printf "\n\n==> Setting web files\n"
    #Import MySQL database
    cat >/docker-entrypoint-initdb.d/000-native_password.sql <<UOJEOF
ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'root';
UOJEOF
    curl $RAW_URL/install/db/app_uoj233.sql >/docker-entrypoint-initdb.d/001-app_uoj233.sql
    curl $RAW_URL/install/judger/add_judger.sql >/docker-entrypoint-initdb.d/002-add_judger.sql
}

echo 'Preparing UOJ System db environment...'
setLAMPConf;setWebConf
