#!/bin/bash
genRandStr(){
    cat /dev/urandom | tr -dc [:alnum:] | head -c $1
}
#Set some vars
_judger_socket_port_="${JUDGER_SOCKET_PORT:-2333}"
_uoj_protocol_="${UOJ_PROTOCOL:-http}"

getAptPackage(){
    printf "\n\n==> Getting environment packages\n"
    # Update apt sources and install
    export DEBIAN_FRONTEND=noninteractive
    dpkg -s gnupg 2>/dev/null || (apt-get update && apt-get install -y gnupg)
    echo "deb http://ppa.launchpad.net/stesie/libv8/ubuntu bionic main" | tee /etc/apt/sources.list.d/stesie-libv8.list && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys D858A0DF
    apt-get update && apt-get install -y git vim ntp zip unzip curl wget apache2 libapache2-mod-xsendfile libapache2-mod-php php php-dev php-pear php-zip php-mysql php-mbstring php-gd php-intl php-xsl g++ make re2c libv8-7.5-dev libyaml-dev
    # Install PHP extensions
    yes | pecl install yaml
    git clone https://github.com/phpv8/v8js.git --depth=1 -b 4c026f3fb291797c109adcabda6aeba6491fe44f /tmp/pear/download/v8js-master && cd /tmp/pear/download/v8js-master
    phpize && ./configure --with-php-config=/usr/bin/php-config --with-v8js=/opt/libv8-7.5 && make install && cd -
}

setLAMPConf(){
    printf "\n\n==> Setting LAMP configs\n"
    #Set Apache UOJ site conf
    cat >/etc/apache2/sites-available/000-uoj.conf <<UOJEOF
<VirtualHost *:80>
    #ServerName local_uoj.ac
    ServerAdmin opensource@uoj.ac
    DocumentRoot /var/www/uoj

    SetEnvIf Request_URI "^/judge/.*$" judgelog
    #LogLevel info ssl:warn
    ErrorLog \${APACHE_LOG_DIR}/uoj_error.log
    CustomLog \${APACHE_LOG_DIR}/uoj_judge.log common env=judgelog
    CustomLog \${APACHE_LOG_DIR}/uoj_access.log combined env=!judgelog

    XSendFile On
    XSendFilePath /var/uoj_data
    XSendFilePath /var/www/uoj/app/storage
    XSendFilePath /opt/uoj/judger/uoj_judger/include
</VirtualHost>
UOJEOF
    #Enable modules and make UOJ site conf enabled
    a2ensite 000-uoj.conf && a2dissite 000-default.conf
    a2enmod rewrite headers && sed -i -e '172s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
    #Create UOJ session save dir and make PHP extensions available
    mkdir --mode=733 /var/lib/php/uoj_sessions && chmod +t /var/lib/php/uoj_sessions
    sed -i -e '912a\extension=v8js.so\nextension=yaml.so' /etc/php/7.4/apache2/php.ini
	sed -i 's|;sys_temp_dir = "/tmp"|sys_temp_dir = "/tmp"|g' /etc/php/7.4/apache2/php.ini
}

setWebConf(){
    printf "\n\n==> Setting web files\n"
    # Set webroot path
    ln -sf /opt/uoj/web /var/www/uoj
    chown -R www-data /var/www/uoj/app/storage
    # Set web config file
    php -a <<UOJEOF
\$config = include '/var/www/uoj/app/.default-config.php';
\$config['judger']['socket']['port']='$_judger_socket_port_';
file_put_contents('/var/www/uoj/app/.config.php', "<?php\nreturn ".str_replace('\'_httpHost_\'','UOJContext::httpHost()',var_export(\$config, true)).";\n");
UOJEOF
    # Prepare local sandbox
    cd /opt/uoj/judger/uoj_judger
    cat >include/uoj_work_path.h <<UOJEOF
#define UOJ_WORK_PATH "/opt/uoj/judger/uoj_judger"
#define UOJ_JUDGER_BASESYSTEM_UBUNTU1804
#define UOJ_JUDGER_PYTHON3_VERSION "3.6"
#define UOJ_JUDGER_FPC_VERSION "3.0.4"
UOJEOF
    make runner -j$(($(nproc) + 1)) && cd /opt/uoj/web
}

initProgress(){
    printf "\n\n==> Doing initial config and start service\n"
    #Set uoj_data path
    mkdir -p /var/uoj_data/upload
    chown -R www-data:www-data /var/uoj_data
    #Using cli upgrade to latest
    php /var/www/uoj/app/cli.php upgrade:latest
    #Start services
    service ntp restart
    service apache2 restart
    #Touch SetupDone flag file
    touch /var/uoj_data/.UOJSetupDone
	mkdir -p /opt/uoj/web/app/storage/submission
	mkdir -p /opt/uoj/web/app/storage/tmp
	mkdir -p /opt/uoj/web/app/storage/paste
	chmod -R 777 /opt/uoj/web/app/storage
    printf "\n\n***Installation complete. Enjoy!***\n"
}

prepProgress(){
    setLAMPConf;setWebConf
}

if [ $# -le 0 ]; then
    echo 'Installing UOJ System web...'
    getAptPackage;prepProgress;initProgress
fi
while [ $# -gt 0 ]; do
    case "$1" in
        -p | --prep)
            echo 'Preparing UOJ System web environment...'
            prepProgress
        ;;
        -i | --init)
            echo 'Initing UOJ System web...'
            initProgress
        ;;
        -? | --*)
            echo "Illegal option $1"
        ;;
    esac
    shift $(( $#>0?1:0 ))
done
