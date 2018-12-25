#!/bin/bash
genRandStr(){
    cat /dev/urandom | tr -dc [:alnum:] | head -c $1
}
#Set some vars
_database_password_=root
_judger_socket_port_=2333
_judger_socket_password_=$(genRandStr 32)
_main_judger_password_=$(genRandStr 32)
_svn_ourroot_password_=$(genRandStr 32)
_svn_certroot_password_=$(genRandStr 32)

getAptPackage(){
    printf "\n\n==> Getting environment packages\n"
    #Set MySQL root password
    export DEBIAN_FRONTEND=noninteractive
    (echo "mysql-server mysql-server/root_password password $_database_password_";echo "mysql-server mysql-server/root_password_again password $_database_password_") | debconf-set-selections
    #Update apt sources and install
    dpkg -s gnupg 2>/dev/null || (apt-get update && apt-get install -y gnupg)
    echo "deb http://ppa.launchpad.net/pinepain/libv8/ubuntu artful main" | tee /etc/apt/sources.list.d/pinepain-libv8.list && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 60C60AA4
    apt-get update && apt-get install -y vim ntp zip unzip curl wget subversion apache2 libapache2-mod-xsendfile libapache2-mod-php php php-dev php-pear php-zip php-mysql php-mbstring mysql-server cmake fp-compiler re2c libv8-6.6-dev libyaml-dev python python3 python-requests
    #Install PHP extensions
    cp -a /opt/libv8*/* /usr && printf "\n\n" | pecl install v8js yaml
}

getOracleJDK(){
    printf "\n\n==> Getting JDK runtime files\n"
    #Add judger user
    useradd -m local_main_judger && usermod -a -G www-data local_main_judger
    #Set Oracle JDK do not track usage
    mkdir -p /etc/oracle/java/ && echo 'com.oracle.usagetracker.track.last.usage=false' >/etc/oracle/java/usagetracker.properties
    #Get newest jdk dist file
    JDK_MIRROR_LINK=https://build.funtoo.org/distfiles/oracle-java/
    curl -s ${JDK_MIRROR_LINK} | grep -oP '>jdk-[7,8].*-linux-x64.tar' | sed -e 's/[\",>]//g' -e 's/-linux-x64.tar//g' >jdkdist.list
    wget --progress=dot:giga ${JDK_MIRROR_LINK}$(sed -n '1p' jdkdist.list)-linux-x64.tar.gz ${JDK_MIRROR_LINK}$(sed -n '$p' jdkdist.list)-linux-x64.tar.gz
    #Change compiler version to faq.php
    sed -i -e 's/Ubuntu Linux 14.04 LTS x64/Ubuntu Linux 18.04 LTS x64/' \
           -e 's/g++ 4.8.4/g++ 7.3.0/' -e 's/gcc 4.8.4/gcc 7.3.0/' -e 's/fpc 2.6.2/fpc 3.0.4/' -e 's/Python 2.7和3.4/Python 2.7和3.6/' \
           -e "s/jdk-7u76/$(sed -n '1p' jdkdist.list)/g" -e "s/jdk-8u31/$(sed -n '$p' jdkdist.list)/g" ../../uoj/1/app/controllers/faq.php
    #Move jdk file to judger user root
    chown local_main_judger jdkdist.list jdk-*-linux-x64.tar.gz
    mv jdkdist.list jdk-*-linux-x64.tar.gz /home/local_main_judger/
}

setLAMPConf(){
    printf "\n\n==> Setting LAMP configs\n"
    #Set Apache UOJ site conf
    cat >/etc/apache2/sites-available/000-uoj.conf <<UOJEOF
<VirtualHost *:80>
    #ServerName local_uoj.ac
    ServerAdmin opensource@uoj.ac
    DocumentRoot /var/www/uoj

    #LogLevel info ssl:warn
    ErrorLog \${APACHE_LOG_DIR}/uoj_error.log
    CustomLog \${APACHE_LOG_DIR}/uoj_access.log combined

    XSendFile On
    XSendFilePath /var/uoj_data
    XSendFilePath /var/www/uoj/app/storage
    XSendFilePath /home/local_main_judger/judge_client/uoj_judger/include
</VirtualHost>
UOJEOF
    #Enable modules and make UOJ site conf enabled
    a2ensite 000-uoj.conf && a2dissite 000-default.conf
    a2enmod rewrite headers && sed -i -e '172s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
    #Create UOJ session save dir and make PHP extensions available
    mkdir --mode=733 /var/lib/php/uoj_sessions && chmod +t /var/lib/php/uoj_sessions
    sed -i -e '865a\extension=v8js.so\nextension=yaml.so' /etc/php/7.2/apache2/php.ini
    #Set MySQL user directory and connection config
    usermod -d /var/lib/mysql/ mysql
    cat >/etc/mysql/mysql.conf.d/uoj_mysqld.cnf <<UOJEOF
[mysqld]
character-set-server=utf8
collation-server=utf8_unicode_ci
init_connect='SET NAMES utf8'
init_connect='SET collation_connection = utf8_unicode_ci'
skip-character-set-client-handshake
sql-mode=ONLY_FULL_GROUP_BY,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
UOJEOF
}

setSVNServe(){
    printf "\n\n==> Setting SVN server\n"
    #Make SVN work dir
    mkdir /var/svn && svnserve -d -r /var/svn
    mkdir /var/svn/problem && chown -R www-data /var/svn/problem
    svnadmin create /var/svn/uoj && svnadmin create /var/svn/judge_client
    #Set SVN server config file and password db
    sed -i -e 's/# store-plaintext-passwords = no/store-plaintext-passwords = yes/g' /etc/subversion/servers
    sed -i -e 's/# anon-access/anon-access/g' -e 's/# auth-access/auth-access/g' -e 's/# password-db/password-db/g' /var/svn/uoj/conf/svnserve.conf
    rm -r /var/svn/judge_client/conf/passwd && ln -s /var/svn/uoj/conf/passwd /var/svn/judge_client/conf/passwd
    cat /var/svn/uoj/conf/svnserve.conf >/var/svn/judge_client/conf/svnserve.conf
    cat >/var/svn/uoj/conf/passwd <<UOJEOF
[users]
root = _svn_certroot_password_
UOJEOF
    #Set SVN hook scripts
    cat >/var/svn/uoj/hooks/post-commit <<UOJEOF
#!/bin/sh
cd /var/svn/uoj/cur/uoj && svn update --username root --password _svn_certroot_password_
UOJEOF
    chmod +x /var/svn/uoj/hooks/post-commit
    cat >/var/svn/judge_client/hooks/post-commit <<UOJEOF
#!/bin/sh
su local_main_judger -c '~/judge_client/judge_client update'
exit \$?
UOJEOF
    chmod +x /var/svn/judge_client/hooks/post-commit
    cat >/var/svn/problem/new_problem.sh <<UOJEOF
if [ \$# -ne 1 ]
then
    echo 'invalid argument'
    exit 1
fi

path=/var/svn/problem/\$1
mkdir \$path
svnadmin create \$path
cat >\$path/conf/svnserve.conf <<EOD
[general]
anon-access = none
auth-access = write
password-db = passwd
EOD

svnusr="our-root"
svnpwd="_svn_ourroot_password_"

cat >\$path/conf/passwd <<EOD
[users]
\$svnusr = \$svnpwd
EOD

mkdir \$path/cur && cd \$path/cur
svn checkout svn://127.0.0.1/problem/\$1 --username \$svnusr --password \$svnpwd
mkdir /var/uoj_data/\$1

cat >\$path/hooks/post-commit <<EODEOD
#!/bin/sh
/var/svn/problem/post-commit.sh \$1
EODEOD
chmod +x \$path/hooks/post-commit
UOJEOF
    chmod +x /var/svn/problem/new_problem.sh
    cat >/var/svn/problem/post-commit.sh <<UOJEOF
#!/bin/sh
svnusr="our-root"
svnpwd="_svn_ourroot_password_"
cd /var/svn/problem/\$1/cur/\$1
svn update --username \$svnusr --password \$svnpwd
chown -R www-data /var/svn/problem/\$1
UOJEOF
    chmod +x /var/svn/problem/post-commit.sh
    #Precheckout to cur folder
    mkdir /var/svn/uoj/cur /var/svn/judge_client/cur
    svn co svn://127.0.0.1/uoj --username root --password '_svn_certroot_password_' /var/svn/uoj/cur/uoj
    svn co svn://127.0.0.1/judge_client --username root --password '_svn_certroot_password_' /var/svn/judge_client/cur/judge_client
    chown -R local_main_judger /var/svn/judge_client/cur/judge_client
}

setWebConf(){
    printf "\n\n==> Setting web files\n"
    #Commit web source file
    svn co svn://127.0.0.1/uoj --username root --password '_svn_certroot_password_'
    cp -r ../../uoj/1 uoj/1 && cd uoj
    svn add 1 && svn ci -m "Installtion commit" --username root --password '_svn_certroot_password_'
    cd .. && rm -r uoj
    #Set webroot path
    ln -sf /var/svn/uoj/cur/uoj/1 /var/www/uoj
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
    mysql -u root --password=$_database_password_ <app_uoj233.sql
}

setJudgeConf(){
    printf "\n\n==> Setting judge_client files\n"
    #Commit judge_client source file
    svn co svn://127.0.0.1/judge_client --username root --password '_svn_certroot_password_'
    cp -r ../../judge_client/1 judge_client/1 && cd judge_client
    svn add 1 && svn ci -m "Installation commit" --username root --password '_svn_certroot_password_'
    cd .. && rm -r judge_client
    #Set uoj_data path
    mkdir /var/uoj_data
    chown -R www-data /var/uoj_data && chgrp -R www-data /var/uoj_data
    #Compile judge_client and set runtime
    su local_main_judger <<EOD
svn update /var/svn/judge_client/cur/judge_client --username root --password '_svn_certroot_password_'
ln -s /var/svn/judge_client/cur/judge_client/1 ~/judge_client
ln -s /var/uoj_data ~/judge_client/uoj_judger/data
cd ~/judge_client && chmod +x judge_client
cat >uoj_judger/include/uoj_work_path.h <<UOJEOF
#define UOJ_WORK_PATH "/home/local_main_judger/judge_client/uoj_judger"
#define UOJ_JUDGER_BASESYSTEM_UBUNTU1804
#define UOJ_JUDGER_PYTHON3_VERSION "3.6"
#define UOJ_JUDGER_FPC_VERSION "3.0.4"
UOJEOF
cd uoj_judger && make -j$(($(grep -c ^processor /proc/cpuinfo) + 1))
mkdir ~/judge_client/uoj_judger/run/runtime && cd ~/judge_client/uoj_judger/run/runtime
mv ~/jdkdist.list ~/jdk-*-linux-x64.tar.gz .
tar -xzf jdk-7*-linux-x64.tar.gz && tar -xzf jdk-8*-linux-x64.tar.gz
mv jdk1.7* jdk1.7.0 && mv jdk1.8* jdk1.8.0
EOD
    #Set judge_client config file
    cat >/home/local_main_judger/judge_client/.conf.json <<UOJEOF
{
    "uoj_protocol": "http",
    "uoj_host": "127.0.0.1",
    "judger_name": "main_judger",
    "judger_password": "_main_judger_password_",
    "socket_port": $_judger_socket_port_,
    "socket_password": "_judger_socket_password_",
    "svn_username": "root",
    "svn_password": "_svn_certroot_password_"
}
UOJEOF
    chmod 600 /home/local_main_judger/judge_client/.conf.json
    chown local_main_judger /home/local_main_judger/judge_client/.conf.json
}

initProgress(){
    printf "\n\n==> Doing initial config and start service\n"
    #Replace password placeholders
    sed -i "s/_svn_ourroot_password_/$_svn_ourroot_password_/g" /var/svn/problem/new_problem.sh /var/svn/problem/post-commit.sh /var/www/uoj/app/.config.php
    sed -i "s/_svn_certroot_password_/$_svn_certroot_password_/g" /var/svn/uoj/conf/passwd /var/svn/uoj/hooks/post-commit /home/local_main_judger/judge_client/.conf.json
    sed -i -e "s/_main_judger_password_/$_main_judger_password_/g" -e "s/_judger_socket_password_/$_judger_socket_password_/g" /home/local_main_judger/judge_client/.conf.json
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
    su local_main_judger -c '~/judge_client/judge_client start'
    #Touch SetupDone flag file
    touch /var/svn/.UOJSetupDone
    printf "\n\n***Installation complete. Enjoy!***\n"
}

prepProgress(){
    getAptPackage;getOracleJDK;setLAMPConf;setSVNServe;setWebConf;setJudgeConf
}

if [ $# -le 0 ] ;then
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
