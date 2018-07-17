From ubuntu:16.04

MAINTAINER Alankrit Srivastava alankrit.srivastava256@webkul.com

##update server

RUN apt-get update \
##install nginx
&& apt-get install -y locales \
&& locale-gen en_US.UTF-8 \
&& export LANG=en_US.UTF-8 \
&& apt-get update \
&& apt-get install -y software-properties-common \
&& LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:nginx/stable \
&& apt-get -y update \
&& apt-get -y install nginx \
&& rm /etc/nginx/sites-enabled/default \
## Generate self signed certificate
&& cd /etc/nginx && echo -e "\n\n\n\n\n\n\n" | openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/cert.key -out /etc/nginx/cert.crt \
##install supervisor and setup supervisord.conf file
&& apt-get install -y supervisor \
&& mkdir -p /var/log/supervisor
Expose 80 443

CMD ["/usr/bin/supervisord"]
