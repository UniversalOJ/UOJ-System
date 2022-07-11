#!/bin/bash

set -e
set -x

id=$1
dir="/var/uoj_data/${id}"
target="/var/uoj_data/transfer/"

mkdir -p $target

cp -r $dir $target