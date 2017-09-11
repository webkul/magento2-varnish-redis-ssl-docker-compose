From ubuntu:14.04

MAINTAINER Alankrit Srivastava alankrit.srivastava256@webkul.com

ARG mysql_password

##update server

RUN apt-get update \

## Install mysql-server

&& DEBIAN_FRONTEND=noninteractive apt-get -y install debconf-utils \

&& echo "mysql-server-5.6 mysql-server/root_password password ${mysql_password}" | debconf-set-selections \

&& echo "mysql-server-5.6 mysql-server/root_password_again password ${mysql_password}" | debconf-set-selections \

&& DEBIAN_FRONTEND=noninteractive  apt-get install -y mysql-server-5.6 \

&& sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/mysql/my.cnf \

##install supervisor and setup supervisord.conf file

&& apt-get install -y supervisor \
  
&& mkdir -p /var/log/supervisor  

VOLUME /var/lib/mysql

Expose 3306

CMD ["/usr/bin/supervisord"]
