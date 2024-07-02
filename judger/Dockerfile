FROM ubuntu:20.04
MAINTAINER Baoshuo <i@baoshuo.ren>
LABEL org.opencontainers.image.source=https://github.com/UniversalOJ/UOJ-System
LABEL org.opencontainers.image.description="UOJ Judger"
LABEL org.opencontainers.image.licenses=MIT

SHELL ["/bin/bash", "-c"]

ENV DEBIAN_FRONTEND=noninteractive
ARG CLONE_ADDFLAG

RUN apt-get update && \
    apt-get install -y --no-install-recommends gnupg ca-certificates apt-transport-https && \
    apt-get update && \
    for pkg in git vim ntp zip unzip curl wget build-essential fp-compiler python python3 python3-requests openjdk-8-jdk openjdk-11-jdk tzdata; do \
        cnt=10 && \
        while ! apt-get install -y "$pkg"; do \
            if [ $cnt -le 0 ]; then \
              echo "Failed to install $pkg" && \
              exit 1; \
            fi; \
            cnt=$((cnt - 1)); \
        done; \
    done

ADD . /opt/uoj_judger
WORKDIR /opt/uoj_judger

# Install environment and set startup script
RUN sh install.sh -p && sh install.sh -d

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
EXPOSE 2333
CMD /opt/up
