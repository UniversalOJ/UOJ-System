FROM ubuntu:20.04
MAINTAINER Baoshuo <i@baoshuo.ren>
LABEL org.opencontainers.image.source=https://github.com/UniversalOJ/UOJ-System
LABEL org.opencontainers.image.description="UOJ Web"
LABEL org.opencontainers.image.licenses=MIT

ARG CLONE_ADDFLAG

ENV DEBIAN_FRONTEND=noninteractive
RUN dpkg -s gnupg 2>/dev/null || (apt-get update && apt-get install -y gnupg) &&\
echo "deb http://ppa.launchpad.net/stesie/libv8/ubuntu bionic main" | tee /etc/apt/sources.list.d/stesie-libv8.list && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys D858A0DF &&\
apt-get update && apt-get install -y git vim ntp zip unzip curl wget apache2 libapache2-mod-xsendfile libapache2-mod-php php php-dev php-pear php-zip php-mysql php-mbstring php-gd php-intl php-xsl g++ make re2c libv8-7.5-dev libyaml-dev &&\
yes | pecl install yaml &&\
git clone https://github.com/phpv8/v8js.git --depth=1 -b 2.1.2 /tmp/pear/download/v8js-master && cd /tmp/pear/download/v8js-master &&\
phpize && ./configure --with-php-config=/usr/bin/php-config --with-v8js=/opt/libv8-7.5 && make install && cd -

ADD . /opt/uoj
WORKDIR /opt/uoj

# Install environment and set startup script
RUN sh web/install.sh -p && echo "\
#!/bin/sh\n\
if [ ! -f \"/var/uoj_data/.UOJSetupDone\" ]; then\n\
  cd /opt/uoj/web && sh install.sh -i\n\
fi\n\
service ntp start\n\
service apache2 start\n\
exec bash\n" >/opt/up && chmod +x /opt/up

ENV LANG=C.UTF-8 TZ=Asia/Shanghai
EXPOSE 80
CMD /opt/up
