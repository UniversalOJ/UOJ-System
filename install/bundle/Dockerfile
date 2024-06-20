FROM ubuntu:20.04
MAINTAINER MascoSkray <MascoSkray@gmail.com>
ARG CLONE_ADDFLAG

WORKDIR /opt
ENV DEBIAN_FRONTEND=noninteractive
#Update apt and install git
RUN apt-get update && apt-get install -y apache2 cmake curl fp-compiler g++ git gnupg libapache2-mod-php libapache2-mod-xsendfile libyaml-dev make mysql-server ntp openjdk-11-jdk openjdk-8-jdk php php-dev php-mbstring php-mysql php-pear php-zip python python3 python3-requests re2c unzip vim wget zip --no-install-recommends
#Clone the latest UOJ Community verison to local
RUN git clone https://github.com/UniversalOJ/UOJ-System.git --depth 1 --single-branch ${CLONE_ADDFLAG} uoj
#Install environment and set startup script
RUN cd uoj/install/bundle && sh install.sh -p && echo "\
#!/bin/sh\n\
rm -rf /run/mysqld/*\n\
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld\n\
if [ ! -f \"/var/uoj_data/.UOJSetupDone\" ]; then\n\
  cd /opt/uoj/install/bundle && sh install.sh -i\n\
fi\n\
service ntp start\n\
service mysql start\n\
service apache2 start\n\
su local_main_judger -c \"/opt/uoj/judger/judge_client start\"\n\
exec bash\n" >/opt/up && chmod +x /opt/up

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
EXPOSE 80 3690
CMD /opt/up
