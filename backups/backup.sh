#!/bin/bash

set -x
## Mention your database container name
container_name=mysql

## Mention your mysql root password
mysql_root_password=rootpassword123

DATE=`date +%F-%H-%M-%S`

for database in `echo 'show databases;' | docker exec -i mysql mysql --user=root --password=$mysql_root_password | grep -v Database | grep -v information_schema | grep -v mysql | grep -v performance_schema`
do
echo $database
docker exec $container_name mysqldump -u root -p$mysql_root_password $database > $database-$DATE.sql && tar -zcvf $database-$DATE.tar.gz $database-$DATE.sql && rm $database-$DATE.sql && echo "$database-$DATE.tar.gz has been created on `date`" >> database_backup.log
done

