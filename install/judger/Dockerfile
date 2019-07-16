FROM ubuntu:18.04
MAINTAINER MascoSkray <MascoSkray@gmail.com>
ARG CLONE_ADDFLAG

WORKDIR /opt
#Update apt and install git
RUN apt-get update && apt-get install -y git
#Clone the latest UOJ Community verison to local
RUN git clone https://github.com/UniversalOJ/UOJ-System.git --depth 1 --single-branch ${CLONE_ADDFLAG} uoj
#Install environment and set startup script
RUN cd uoj/install/judger && sh install.sh -p && echo "\
#!/bin/sh\n\
if [ ! -f \"/opt/uoj/judger/.conf.json\" ]; then\n\
  cd /opt/uoj/install/judger && sh install.sh -i\n\
fi\n\
service ntp start\n\
su judger -c \"/opt/uoj/judger/judge_client start\"\n\
exec bash\n" >/opt/up && chmod +x /opt/up

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
EXPOSE 2333
CMD /opt/up
