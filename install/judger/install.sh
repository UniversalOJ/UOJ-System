#!/bin/bash

getAptPackage(){
    printf "\n\n==> Getting environment packages\n"
    export DEBIAN_FRONTEND=noninteractive
    apt-get update && apt-get install -y vim ntp zip unzip curl wget build-essential fp-compiler python python3 python3-requests openjdk-8-jdk openjdk-11-jdk
}

setJudgeConf(){
    printf "\n\n==> Setting judger files\n"
    #Add judger user
    adduser judger --gecos "" --disabled-password
    #Set uoj_data path
    mkdir /var/uoj_data_copy && chown judger /var/uoj_data_copy
    #Compile uoj_judger and set runtime
    chown -R judger:judger /opt/uoj/judger
    su judger <<EOD
ln -s /var/uoj_data_copy /opt/uoj/judger/uoj_judger/data
cd /opt/uoj/judger && chmod +x judge_client
cat >uoj_judger/include/uoj_work_path.h <<UOJEOF
#define UOJ_WORK_PATH "/opt/uoj/judger/uoj_judger"
#define UOJ_JUDGER_BASESYSTEM_UBUNTU1804
#define UOJ_JUDGER_PYTHON3_VERSION "3.6"
#define UOJ_JUDGER_FPC_VERSION "3.0.4"
UOJEOF
cd uoj_judger && make -j$(($(nproc) + 1))
EOD
}

initProgress(){
    printf "\n\n==> Doing initial config and start service\n"
    #Check envs
    if [ -z "$UOJ_PROTOCOL" -o -z "$UOJ_HOST" -o -z "$JUDGER_NAME" -o -z "$JUDGER_PASSWORD" -o -z "$SOCKET_PORT" -o -z "$SOCKET_PASSWORD" ]; then
        echo "!! Environment variables not set! Please edit config file by yourself!"
    else
        #Set judge_client config file
        cat >../../judger/.conf.json <<UOJEOF
{
    "uoj_protocol": "$UOJ_PROTOCOL",
    "uoj_host": "$UOJ_HOST",
    "judger_name": "$JUDGER_NAME",
    "judger_password": "$JUDGER_PASSWORD",
    "socket_port": $SOCKET_PORT,
    "socket_password": "$SOCKET_PASSWORD"
}
UOJEOF
        chmod 600 ../../judger/.conf.json && chown judger ../../judger/.conf.json
        chown -R judger:judger ../../judger/log
        #Start services
        service ntp restart
        su judger -c '/opt/uoj/judger/judge_client start'
        echo "please modify the database after getting the judger server ready:"
        echo "insert into judger_info (judger_name, password, ip) values ('$JUDGER_NAME', '$JUDGER_PASSWORD', '__judger_ip_here__');"
        printf "\n\n***Installation complete. Enjoy!***\n"
    fi
}

prepProgress(){
    getAptPackage;setJudgeConf
}

if [ $# -le 0 ]; then
    echo 'Installing UOJ System judger...'
    prepProgress;initProgress
fi
while [ $# -gt 0 ]; do
    case "$1" in
        -p | --prep)
            echo 'Preparing UOJ System judger environment...'
            prepProgress
        ;;
        -i | --init)
            echo 'Initing UOJ System judger...'
            initProgress
        ;;
        -? | --*)
            echo "Illegal option $1"
        ;;
    esac
    shift $(( $#>0?1:0 ))
done
