From ubuntu:16.04

MAINTAINER Alankrit Srivastava alankrit.srivastava256@webkul.com

##update server

RUN apt-get update \
##install supervisor and setup supervisord.conf file
&& apt-get install -y supervisor \
&& mkdir -p /var/log/supervisor \
##install varnish
&& apt-get -y install varnish \
&& rm /etc/varnish/default.vcl \
&& rm /etc/default/varnish 
EXPOSE 6082 6081
CMD ["/usr/bin/supervisord"]
