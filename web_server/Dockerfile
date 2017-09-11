From ubuntu:14.04

MAINTAINER Alankrit Srivastava alankrit.srivastava256@webkul.com

##update server

RUN apt-get update \

##install apache2 and other required packages

&& apt-get -y install apache2 \

&& locale-gen en_US.UTF-8 \
	&& export LANG=en_US.UTF-8 \
	&& apt-get update \
	&& apt-get install -y software-properties-common \
	&& LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:ondrej/php \
	&& apt-get update \
        && apt-get install -y python-software-properties \
        && apt-get -y install php7.0 php7.0-curl php7.0-intl php7.0-gd php7.0-dom php7.0-mcrypt php7.0-iconv php7.0-xsl php7.0-mbstring php7.0-ctype php7.0-zip php7.0-pdo php7.0-xml php7.0-bz2 php7.0-calendar php7.0-exif php7.0-fileinfo php7.0-json php7.0-mysqli php7.0-mysql php7.0-posix php7.0-tokenizer php7.0-xmlwriter php7.0-xmlreader php7.0-phar php7.0-soap php7.0-mysql php7.0-fpm libapache2-mod-php7.0 \
## manage php memory and date-timezone setttings

&& sed -i -e"s/^memory_limit\s*=\s*128M/memory_limit = 512M/" /etc/php/7.0/apache2/php.ini \

&& sed -i "/Listen 80/d" /etc/apache2/ports.conf \

&& echo "Listen 8080" >> /etc/apache2/ports.conf \

&& a2enmod php7.0 \

&& a2enmod rewrite \

&& a2enmod headers \

&& rm /etc/apache2/sites-enabled/000-default.conf /var/www/html/* \

##install supervisor and setup supervisord.conf file

&& apt-get install -y supervisor \
 
&& mkdir -p /var/log/supervisor

Expose 8080

CMD ["/usr/bin/supervisord"]
