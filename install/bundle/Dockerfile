FROM ubuntu:18.04
MAINTAINER MascoSkray <MascoSkray@gmail.com>

#Update apt and install git
RUN apt-get update && apt-get install -y git
#Clone the latest UOJ Community verison to local
RUN cd ~ && git clone https://github.com/UniversalOJ/UOJ-System.git --depth 1
#Install environment and set startup script
RUN cd ~/UOJ-System/install/bundle && sh install.sh -p && echo "\
#!/bin/sh\n\
chown -R mysql:mysql /var/lib/mysql /var/run/mysqld\n\
if [ ! -f \"/var/svn/.UOJSetupDone\" ]; then\n\
  cd ~/UOJ-System/install/bundle && sh install.sh -i\n\
fi\n\
service ntp start\n\
service mysql start\n\
service apache2 start\n\
svnserve -d -r /var/svn\n\
su local_main_judger -c \"~/judge_client/judge_client start\"\n\
exec bash\n" >/root/up && chmod +x /root/up

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
EXPOSE 80 3690
CMD /root/up
