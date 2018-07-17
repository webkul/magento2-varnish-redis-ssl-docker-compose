#!/bin/bash

set -u
## Mention your database container name
container_name=mysql

## Mention mysql root password

MYSQL_ROOT_PASSWORD=mention_your_mysql_root_password

DATE=`date +%F-%H-%M-%S`

for database in `echo 'show databases;' | docker exec -i mysql mysql --user=root --password=$MYSQL_ROOT_PASSWORD | grep -v Database | grep -v information_schema | grep -v mysql | grep -v performance_schema`
do
echo $database
docker exec $container_name mysqldump -u root -p$MYSQL_ROOT_PASSWORD $database > $database-$DATE.sql && tar -zcvf $database-$DATE.tar.gz $database-$DATE.sql && rm $database-$DATE.sql && echo "$database-$DATE.tar.gz has been created on `date`" >> database_backup.log
done

