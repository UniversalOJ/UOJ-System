FROM mysql:5.7
MAINTAINER Baoshuo <i@baoshuo.ren>
LABEL org.opencontainers.image.source=https://github.com/UniversalOJ/UOJ-System
LABEL org.opencontainers.image.description="UOJ Database"
LABEL org.opencontainers.image.licenses=MIT

ADD . /opt/uoj_db
WORKDIR /opt/uoj_db

RUN sh install.sh

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
