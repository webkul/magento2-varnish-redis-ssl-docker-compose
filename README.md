### Optimising Magento 2 with Varnish Cache, Redis and Nginx SSL termination on the Multi-container Architecture Using Docker-Compose tool.

This repository corresponds to architecture setup as mentioned in blog https://cloudkul.com/blog/integrate-magento-2-varnish-cache-redis-server-ssl-termination-using-docker-compose/ .

##### Docker-Compose Tool

As mentioned in Docker docs, Compose is a tool for defining and running multi-container Docker applications. With Compose, you use a Compose file to configure your application’s services. Then, using a single command, you create and start all the services from your configuration. 

With the help of docker-compose we can define containers to be built, their configuration, links, volumes, ports etc in a single file and it gets launched by a single command. We can add multiple servers and services just by adding them to docker-compose configuration file. This configuration file is in YAML format.

Getting started with docker-compose is a few steps process:

> Create a Dockerfile defining the application environment. We can create separate Dockerfile for our different services. As Dockerfile are lightweight, so our application can be replicated anywhere.

> Create a docker-compose.yml file defining services that needed for application run. We can define volumes to be mapped, ports to be exposed, links to be created, arguments to be passed etc in our docker-compose.yml file.

> Run ‘docker-compose build’ to create Docker image. After creating Dockerfile, docker-compose.yml and placing our volumes at right places, we can create our image.

> Run ‘docker-compose up -d’ to run the docker containers. After image build up, we can run all of our containers as mentioned in configuration files by this single command.

##### Dockerizing Magento 2, Varnish Cache, Redis server over SSL with Docker-Compose

Docker is an open-source project that can be integrated with almost all the applications allowing scope of isolation and flexibility. It can be integrated with Magento 2 as well. Magento is an e-commerce platform written in PHP and based on zend framework available under both open-source and commercial licenses.

Varnish cache is a web application accelerator also known as a caching HTTP reverse proxy. Varnish cache visits your server once to cache the page, then all future requests for the same page will be served by Varnish cache. Varnish acts a reverse proxy server that directs client requests to the back-end apache2 server. Whenever a client makes a request, Varnish server checks the content within the cache and in case data not found, it sends the request to backend server and fetch the content to client and keep a copy of the data as cache. When the same request is made, Varnish does not bother apache2 server, it just fetch the data from the cache. It provides an additional level of abstraction and control to ensure the smooth flow of network traffic between clients and servers.

Redis is an open source, BSD licensed, advanced key-value store that can optionally be used in Magento for back end and session storage. When first time page is loaded, a database is queried on the server. Redis caches the query. Next time other user loads the page the results are provided from the redis without quering the actual database. 

Magento 2 works out of box with Varnish Cache and provides its own VCL file for its setup. Magento supports many backend caches like MemcacheD and APC that are commonly used. However, Redis has become a popular and powerful cache system for Magento and other web applications. 

Nginx servers as reverse proxy server that receives traffic on port 80 and 443 and then proxy pass it to listening port of Varnish Cache server. It is done to deploy a way to direct both HTTP and HTTPS traffic to Varnish cache server which in turn, if needed, forward it apache2 server.

In this project, we are using:

> Operating system: Ubuntu 16.04

> Web Server: Apache2

> Database Server: Mysql-server-5.7

> Cache Server: Varnish 4.1

> PHP version: PHP-7.1

> Redis server: Redis 

> SSL server: Nginx 1.10.1

To begin with, please install docker and docker-compose on your ubuntu server. 

Then follow the following steps:

1). Clone or download this repository as 


> git clone https://github.com/webkul/magento2-varnish-redis-ssl-docker-compose.git.

2) Set mysql root credentials and name of the database to be created in *database_server* block ~/magento2-varnish-redis-ssl-docker-compose/docker-compose.yml:

> mysql_password=

> mysql_database=

3). Download Magento 2 version you wish to dockerize and upload it in directory magento2 in parallel docker-compose.yml.

> Go to https://magento.com/tech-resources/download? .

4). Replace localhost in 'server_name' in ~/magento2-varnish-redis-ssl-docker-compose/ssl_server/default with your domain name or IP address.

5). Build the docker image.

> docker-compose build

6). Check the built image as:

> docker images

7). Run the containers from built image as:

> docker-compose up -d

8). Check the running docker containers by command:

> docker-compose ps

> docker ps


Now, your server setup is all ready, now hit your domain name or IP to install Magento 2. Now to configure Varnish for Magento 2 and test its working, please refer to blog https://cloudkul.com/blog/magento-2-and-varnish-cache-integration-with-docker-compose/.

> Use name or id of the mysql container as database host.

To configure Magento 2 for redis-server, please refer to blog https://cloudkul.com/blog/integrate-magento-2-varnish-cache-redis-server-ssl-termination-using-docker-compose/ .


##### Backing up Databases from Mysql-Server container

Although we had secured our application code keeping it on our host but database is as important as server code. So in order to keep their backup we schedule a shell script that will take backups of all the databases present in mysql-server container and keep them in archived from on our host. Shell script is present on ~/magento2-varnish-redis-ssl-docker-compose/backups/db_backup.sh. Please refer to blog https://cloudkul.com/blog/integrate-magento-2-varnish-cache-redis-server-ssl-termination-using-docker-compose/  for backup management.

If you face any issues, kindly report back.


#### GETTING SUPPORT

If you have any issues, contact us at support@webkul.com or raise ticket at https://webkul.uvdesk.com/

