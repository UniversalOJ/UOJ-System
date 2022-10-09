#!/bin/bash
genRandStr(){
    cat /dev/urandom | tr -dc [:alnum:] | head -c $1
}
#Set some vars
_database_password_=root
_judger_socket_port_=2333
_judger_socket_password_=$(genRandStr 32)
_main_judger_password_=$(genRandStr 32)

getAptPackage(){
    printf "\n\n==> Getting environment packages\n"
    #Set MySQL root password
    export DEBIAN_FRONTEND=noninteractive
    (echo "mysql-server mysql-server/root_password password $_database_password_";echo "mysql-server mysql-server/root_password_again password $_database_password_") | debconf-set-selections
    #Update apt sources and install
    add-apt-repository ppa:stesie/libv8 -y
    echo "deb http://ppa.launchpad.net/stesie/libv8/ubuntu bionic main" | tee /etc/apt/sources.list.d/stesie-libv8.list
    add-apt-repository ppa:ondrej/php -y
    find /etc/apt/sources.list.d/ -type f -name "*.list" -exec  sed  -i.bak -r  's#deb(-src)?\s*http(s)?://ppa.launchpad.net#deb\1 http\2://launchpad.proxy.ustclug.org#ig' {} \;
    find /etc/apt/sources.list.d/ -type f -name "*.list" -exec  sed  -i.bak -r  's#deb(-src)?\s*http(s)?://ppa.launchpadcontent.net#deb\1 http\2://launchpad.proxy.ustclug.org#ig' {} \;
    apt-get update
    apt-get install -y libv8 php7.4 php7.4-yaml php7.4-xml php7.4-dev php7.4-zip php7.4-mysql php7.4-mbstring
    apt-get install -y libseccomp-dev git vim ntp zip unzip curl wget libapache2-mod-xsendfile mysql-server php-pear cmake fp-compiler re2c libv8-7.5-dev libyaml-dev python2.7 python3 python3-requests openjdk-8-jdk openjdk-11-jdk openjdk-17-jdk
    ln -s /bin/python2.7 /usr/bin/python
    #Install PHP extensions
    yes | pecl install yaml
    git clone https://hub.fastgit.xyz/phpv8/v8js.git /tmp/pear/download/v8js-master
    cd /tmp/pear/download/v8js-master
    git checkout acd9431ec9d8212f6503490639bc7997c9488c46 && phpize && ./configure --with-php-config=/usr/bin/php-config --with-v8js=/opt/libv8-7.5 && make install && cd -
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
    XSendFilePath /var/uoj_data_copy
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
    #Set MySQL user directory and connection config
    usermod -d /var/lib/mysql/ mysql
    cat >/etc/mysql/mysql.conf.d/uoj_mysqld.cnf <<UOJEOF
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
    #Set webroot path
    ln -sf /opt/uoj/web /var/www/uoj
    chown -R www-data /var/www/uoj/app/storage
    #Set web config file
    php -a <<UOJEOF
\$config = include '/var/www/uoj/app/.default-config.php';
\$config['database']['password']='$_database_password_';
\$config['judger']['socket']['port']='$_judger_socket_port_';
file_put_contents('/var/www/uoj/app/.config.php', "<?php\nreturn ".str_replace('\'_httpHost_\'','UOJContext::httpHost()',var_export(\$config, true)).";\n");
UOJEOF
    #Import MySQL database
    service mysql restart
    mysql -u root --password=$_database_password_ < /opt/uoj/install/db/app_uoj233.sql
    echo "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$_database_password_';" | mysql -u root --password=$_database_password_
}

setJudgeConf(){
    printf "\n\n==> Setting judger files\n"
    #Add local_main_judger user
    useradd -m local_main_judger && usermod -a -G www-data local_main_judger
    #Set uoj_data path
    mkdir -p /var/uoj_data/upload
    chown -R www-data:www-data /var/uoj_data
    mkdir -p /var/uoj_data_copy/
    chown -R local_main_judger:www-data /var/uoj_data_copy
    #Compile uoj_judger and set runtime
    chown -R local_main_judger:local_main_judger /opt/uoj/judger
    ln -s /var/uoj_data /opt/uoj/judger/uoj_judger/data
    ln -s /var/uoj_data_copy /opt/uoj/judger/uoj_judger/data_copy
    su local_main_judger <<EOD
cd /opt/uoj/judger && chmod +x judge_client
cd uoj_judger && make -j$(($(nproc) + 1))
EOD
    #Set judge_client config file
    cat >/opt/uoj/judger/.conf.json <<UOJEOF
{
    "uoj_protocol": "http",
    "uoj_host": "127.0.0.1",
    "judger_name": "main_judger",
    "judger_password": "_main_judger_password_",
    "socket_port": $_judger_socket_port_,
    "socket_password": "_judger_socket_password_"
}
UOJEOF
    chmod 600 /opt/uoj/judger/.conf.json && chown local_main_judger /opt/uoj/judger/.conf.json
}

initProgress(){
    printf "\n\n==> Doing initial config and start service\n"
    #Replace password placeholders
    sed -i -e "s/_main_judger_password_/$_main_judger_password_/g" -e "s/_judger_socket_password_/$_judger_socket_password_/g" /opt/uoj/judger/.conf.json
    sed -i -e "s/salt0/$(genRandStr 32)/g" -e "s/salt1/$(genRandStr 16)/g" -e "s/salt2/$(genRandStr 16)/g" -e "s/salt3/$(genRandStr 16)/g" -e "s/_judger_socket_password_/$_judger_socket_password_/g" /var/www/uoj/app/.config.php
    #Import judge_client to MySQL database
    service mysql start
    echo "insert into judger_info (judger_name, password) values (\"main_judger\", \"$_main_judger_password_\")" | mysql app_uoj233 -u root --password=$_database_password_
    #Using cli upgrade to latest
    php /var/www/uoj/app/cli.php upgrade:latest
    #Start services
    service ntp restart
    service mysql restart
    service apache2 restart
    su local_main_judger -c '/opt/uoj/judger/judge_client start'
    #Touch SetupDone flag file
    touch /var/uoj_data/.UOJSetupDone
    printf "\n\n***Installation complete. Enjoy!***\n"
}

prepProgress(){
    getAptPackage;setLAMPConf;setWebConf;setJudgeConf
}

if [ $# -le 0 ]; then
    echo 'Installing UOJ System bundle...'
    prepProgress;initProgress
fi
while [ $# -gt 0 ]; do
    case "$1" in
        -p | --prep)
            echo 'Preparing UOJ System bundle environment...'
            prepProgress
        ;;
        -i | --init)
            echo 'Initing UOJ System bundle...'
            initProgress
        ;;
        -? | --*)
            echo "Illegal option $1"
        ;;
    esac
    shift $(( $#>0?1:0 ))
done