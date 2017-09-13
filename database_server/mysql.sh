#!/bin/bash

set -x
database_name=magento_db                    ## Mention database name
database_user=magento_user                  ## Mention database user
database_root_password=rootpassword123      ## Mention mysql root password.

## Same password must be passed as argument during image build.

## Database user password will be randomly generated

database_user_password=`date | md5sum | fold -w 12 | head -n 1`

## Database user password will be stored in /var/log/check.log ##
## Remove /var/log/check.log after retrieving password .       ##

database_availability_check=none

while [ "$database_availability_check" != "$database_name" ]; do
database_availability_check=`mysqlshow --user=root --password=$database_root_password | grep -ow $database_name`
mysql -u root -p$database_root_password -e "grant all on *.* to 'root'@'%' identified by '$database_root_password';"
mysql -u root -p$database_root_password -e "create database $database_name;"
mysql -u root -p$database_root_password -e "grant all on $database_name.* to '$database_user'@'%' identified by '$database_user_password';"
echo "Your database user "$database_user" password for database "$database_name" is "$database_user_password"" > /var/log/check.log
done


