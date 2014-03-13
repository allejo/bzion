#!/usr/bin/env bash

composer install

FILE=bzion-config.php

cp bzion-config-example.php $FILE

mysql -e "CREATE DATABASE IF NOT EXISTS bzion;" -uroot;
mysql -uroot bzion < DATABASE.sql

sed -i 's/bzion_admin/root/' $FILE
sed -i 's/password//' $FILE
sed -i 's/\$_SERVER\[\"HTTP_HOST\"\]/\"http:\/\/localhost\/bzion\"/' $FILE
sed -i 's/"DEVELOPMENT", FALSE/"DEVELOPMENT", TRUE/' $FILE

echo "error_reporting (E_ALL | E_STRICT | E_DEPRECATED);" >> $FILE
