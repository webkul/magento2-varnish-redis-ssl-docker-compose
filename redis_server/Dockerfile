From ubuntu:16.04

MAINTAINER Alankrit Srivastava alankrit.srivastava256@webkul.com

##update server

RUN apt-get update \
&& apt-get install -y locales \
&& locale-gen en_US.UTF-8 \
&& export LANG=en_US.UTF-8 \
&& apt-get update \
&& apt-get install -y software-properties-common \
&& LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:chris-lea/redis-server \
&& apt-get update \
&& apt-get -y install redis-server \
&& sed -i -e"s/^bind\s127.0.0.1/bind 0.0.0.0/" /etc/redis/redis.conf \
&& chown -R redis: /var/log/redis/ \
##install supervisor and setup supervisord.conf file
&& apt-get install -y supervisor \
&& mkdir -p /var/log/supervisor 
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
EXPOSE 6379
CMD ["/usr/bin/supervisord"]
