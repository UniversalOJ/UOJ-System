#!/bin/bash
genRandStr(){
	cat /dev/urandom | tr -dc [:alnum:] | head -c $1
}
#Set some vars
_database_password_=root
_judger_socket_port_=2333
_judger_socket_password_=$(genRandStr 10)
_main_judger_password_=$(genRandStr 32)
_svn_certroot_password_=$(genRandStr 32)

getAptPackage(){
	echo -e "\n\n==> Getting environment packages"
	#Set MySQL root password
	debconf-set-selections <<< "mysql-server-5.6 mysql-server/root_password password $_database_password_" && debconf-set-selections <<< "mysql-server-5.6 mysql-server/root_password_again password $_database_password_"
	#Update apt sources and install
	apt-get update -y
	apt-get install -y vim ntp zip unzip curl wget subversion apache2 libapache2-mod-xsendfile libapache2-mod-auth-mysql php5 php5-dev php-pear php5-mysql mysql-server-5.6 cmake fp-compiler re2c libv8-dev libyaml-dev python python3 python-requests
	#Install PHP extensions
	echo -e "\n\n" | pecl install v8js-0.1.3 yaml-1.3.1
}

getOracleJDK(){
	echo -e "\n\n==> Getting JDK runtime files"
	#Add judger user
	useradd -m local_main_judger && usermod -a -G www-data local_main_judger
	#Get newest jdk dist file
	JDK_MIRROR_LINK=http://ftp.osuosl.org/pub/funtoo/distfiles/oracle-java/
	JDK_CNMIRROR_LINK=http://funtoo.neu.edu.cn/funtoo/distfiles/oracle-java/
	curl -s ${JDK_MIRROR_LINK} | grep -oP '>jdk-[7,8].*-linux-x64.tar' | sed -e 's/[\",>]//g' -e 's/-linux-x64.tar//g' >jdkdist.list
	wget ${JDK_MIRROR_LINK}$(sed -n '1p' jdkdist.list)-linux-x64.tar.gz && wget ${JDK_MIRROR_LINK}$(sed -n '2p' jdkdist.list)-linux-x64.tar.gz
	#Change jdk version to faq.php
	sed -i -e "s/jdk-7u76/$(sed -n '1p' jdkdist.list)/g" -e "s/jdk-8u31/$(sed -n '2p' jdkdist.list)/g" ../../uoj/1/app/controllers/faq.php
	#Move jdk file to judge user root
	chown local_main_judger jdkdist.list jdk-*-linux-x64.tar.gz
	mv jdkdist.list jdk-*-linux-x64.tar.gz /home/local_main_judger/
}

setLAMPConf(){
	echo -e "\n\n==> Setting LAMP configs"
	#Set Apache UOJ site conf
	cat >/etc/apache2/sites-available/000-uoj.conf <<UOJEOF
<VirtualHost *:80>
	#ServerName local_uoj.ac
	ServerAdmin vfleaking@163.com
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
	a2enmod rewrite	headers && sed -i -e '166s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
	#Create UOJ session save dir and make PHP extensions available
	mkdir --mode=733 /var/lib/php5/uoj && chmod +t /var/lib/php5/uoj
	sed -i -e '866a\extension=v8js.so\nextension=yaml.so' /etc/php5/apache2/php.ini
	#Set MySQL user directory and connection config
	usermod -d /var/lib/mysql/ mysql
	sed -i -e "108a\init_connect='SET collation_connection = utf8_unicode_ci'\ninit_connect='SET NAMES utf8'\ncharacter-set-server=utf8\ncollation-server=utf8_unicode_ci\nskip-character-set-client-handshake" -e "117a\default-character-set=utf8" /etc/mysql/my.cnf
}

setSVNServe(){
	echo -e "\n\n==> Setting SVN server"
	#Make SVN work dir
	mkdir /var/svn && svnserve -d -r /var/svn
	mkdir /var/svn/problem && chown www-data /var/svn/problem -R
	svnadmin create /var/svn/uoj && svnadmin create /var/svn/judge_client
	#Set SVN server config file and password db
	sed -i -e 's/# store-plaintext-passwords = no/store-plaintext-passwords = yes/g' /etc/subversion/servers
	sed -i -e 's/# anon-access/anon-access/g' -e 's/# auth-access/auth-access/g' -e 's/# password-db/password-db/g' /var/svn/uoj/conf/svnserve.conf
	rm -r /var/svn/judge_client/conf/passwd && ln -s /var/svn/uoj/conf/passwd /var/svn/judge_client/conf/passwd
	cat /var/svn/uoj/conf/svnserve.conf >/var/svn/judge_client/conf/svnserve.conf
	cat >/var/svn/uoj/conf/passwd <<UOJEOF
[users]
root = $_svn_certroot_password_
UOJEOF
	#Set SVN hook scripts
	cat >/var/svn/uoj/hooks/post-commit <<UOJEOF
#!/bin/sh
cd /var/svn/uoj/cur/uoj && svn update --username root --password $_svn_certroot_password_
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
svnpwd="$_svn_certroot_password_"

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
svnpwd="$_svn_certroot_password_"
cd /var/svn/problem/\$1/cur/\$1
svn update --username \$svnusr --password \$svnpwd
chown www-data /var/svn/problem/\$1 -R
UOJEOF
	chmod +x /var/svn/problem/post-commit.sh
	#Precheckout to cur folder
	mkdir /var/svn/uoj/cur /var/svn/judge_client/cur
	svn co svn://127.0.0.1/uoj --username root --password $_svn_certroot_password_ /var/svn/uoj/cur/uoj
	svn co svn://127.0.0.1/judge_client --username root --password $_svn_certroot_password_ /var/svn/judge_client/cur/judge_client
	chown local_main_judger /var/svn/judge_client/cur/judge_client -R
}

setWebConf(){
	echo -e "\n\n==> Setting web files"
	#Commit web source file
	svn co svn://127.0.0.1/uoj --username root --password $_svn_certroot_password_
	mv ../../uoj/1 uoj/1 && cd uoj
	svn add 1 && svn ci -m "Installtion commit" --username root --password $_svn_certroot_password_
	cd .. && rm uoj /var/www/uoj -r
	#Set webroot path
	ln -s /var/svn/uoj/cur/uoj/1 /var/www/uoj
	chown www-data /var/www/uoj/app/storage -R
	#Set web config file
	php -a <<UOJEOF
\$config = include '/var/www/uoj/app/.default-config.php';
\$config['database']['password']='$_database_password_';
\$config['security']['user']['client_salt']='$(genRandStr 32)';
\$config['security']['cookie']['checksum_salt']=['$(genRandStr 16)','$(genRandStr 16)','$(genRandStr 16)'];
\$config['judger']['socket']['port']='$_judger_socket_port_';
\$config['judger']['socket']['password']='$_judger_socket_password_';
\$config['svn']['our-root']['password']='$_svn_certroot_password_';
file_put_contents('/var/www/uoj/app/.config.php', "<?php\nreturn ".str_replace('\'_httpHost_\'','UOJContext::httpHost()',var_export(\$config, true)).";\n");
UOJEOF
	#Import MySQL database
	service mysql start
	mysql -u root --password=$_database_password_ <app_uoj233.sql
}

setJudgeConf(){
	echo -e "\n\n==> Setting judge_client files"
	#Commit judge_client source file
	svn co svn://127.0.0.1/judge_client --username root --password $_svn_certroot_password_
	mv ../../judge_client/1 judge_client/1 && cd judge_client
	svn add 1 && svn ci -m "Installation commit" --username root --password $_svn_certroot_password_
	cd .. && rm judge_client -r
	#Set uoj_data path
	mkdir /var/uoj_data
	chown www-data /var/uoj_data -R && chgrp www-data /var/uoj_data -R
	#Compile judge_client and set runtime
	su local_main_judger <<EOD
svn update /var/svn/judge_client/cur/judge_client --username root --password $_svn_certroot_password_
ln -s /var/svn/judge_client/cur/judge_client/1 ~/judge_client
ln -s /var/uoj_data ~/judge_client/uoj_judger/data
cd ~/judge_client && chmod +x judge_client
echo '#define UOJ_WORK_PATH "/home/local_main_judger/judge_client/uoj_judger"' >uoj_judger/include/uoj_work_path.h
make
mkdir ~/judge_client/uoj_judger/run/runtime && cd ~/judge_client/uoj_judger/run/runtime
mv ~/jdkdist.list ~/jdk-*-linux-x64.tar.gz .
tar -xzf jdk-7*-linux-x64.tar.gz && tar -xzf jdk-8*-linux-x64.tar.gz
mv jdk1.7* jdk1.7.0_latest && mv jdk1.8* jdk1.8.0_latest
EOD
	#Set judge_client config file
	cat >/home/local_main_judger/judge_client/.conf.json <<UOJEOF
{
	"uoj_protocol": "http",
	"uoj_host": "127.0.0.1",
	"judger_name": "main_judger",
	"judger_password": "$_main_judger_password_",
	"socket_port": $_judger_socket_port_,
	"socket_password": "$_judger_socket_password_",
	"svn_username": "root",
	"svn_password": "$_svn_certroot_password_"
}
UOJEOF
	chmod 600 /home/local_main_judger/judge_client/.conf.json
	chown local_main_judger /home/local_main_judger/judge_client/.conf.json
	#Import judge_client to MySQL database
	echo "insert into judger_info (judger_name, password) values (\"main_judger\", \"$_main_judger_password_\")" | mysql app_uoj233 -u root --password=$_database_password_
}

endUpProgress(){
	echo -e "\n\n==> Ending progress and start service"
	#Using cli upgrade to latest
	php /var/www/uoj/app/cli.php upgrade:latest
	#Start services
	service ntp restart
	service mysql restart
	service apache2 restart
	su local_main_judger -c '~/judge_client/judge_client start'
	#Set SetupDone flag file
	echo 'Congratulations!' > /var/svn/.UOJSetupDone
	echo -e "\n\n***Installation complete. Enjoy!***"
}

if [ "$1" == "-e" ] ;then
	echo 'Setting UOJ System bundle environment...'
	getAptPackage
	getOracleJDK
	setLAMPConf
elif [ "$1" == "-c" ] ;then
	echo 'Configuring UOJ System bundle...'
	setSVNServe
	setWebConf
	setJudgeConf
	endUpProgress
else
	echo 'Installing UOJ System bundle...'
	getAptPackage
	getOracleJDK
	setLAMPConf
	setSVNServe
	setWebConf
	setJudgeConf
	endUpProgress
fi
