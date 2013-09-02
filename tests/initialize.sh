#!/usr/bin/env bash

FILE=bzion-config.php

cp bzion-config-example.php $FILE

mysql -e "CREATE DATABASE IF NOT EXISTS bzion;" -uroot;
mysql -uroot bzion < DATABASE.sql

sed -i 's/bzion_admin/root/' $FILE
sed -i 's/password//' $FILE
sed -i 's/\$_SERVER\[\"HTTP_HOST\"\]/\"http:\/\/localhost\/bzion\"/' $FILE
sed -i 's/"DEVELOPMENT", FALSE/"DEVELOPMENT", TRUE/' $FILE

echo "error_reporting (E_ALL | E_STRICT | E_DEPRECATED);" >> $FILE
echo 'ini_set("log_errors", 1);' >> $FILE
echo 'ini_set("error_log", "error.log");' >> $FILE
