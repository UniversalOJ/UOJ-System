FROM ubuntu:18.04
MAINTAINER MascoSkray <MascoSkray@gmail.com>
ARG CLONE_ADDFLAG

WORKDIR /opt
#Update apt and install git
RUN apt-get update && apt-get install -y git
#Clone the latest UOJ Community verison to local
RUN git clone https://github.com/UniversalOJ/UOJ-System.git --depth 1 --single-branch ${CLONE_ADDFLAG} uoj
#Install environment and set startup script
RUN cd uoj/install/web && sh install.sh -p && echo "\
#!/bin/sh\n\
if [ ! -f \"/var/uoj_data/.UOJSetupDone\" ]; then\n\
  cd /opt/uoj/install/web && sh install.sh -i\n\
fi\n\
service ntp start\n\
service apache2 start\n\
exec bash\n" >/opt/up && chmod +x /opt/up

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
EXPOSE 80 3690
CMD /opt/up
