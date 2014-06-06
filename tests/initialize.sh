#!/usr/bin/env bash

COMPOSER_ARGS="--no-interaction"
if [[ $BZION_HHVM -eq 1 ]]; then
   COMPOSER_ARGS="-v"
fi

php composer.phar install $COMPOSER_ARGS

FILE=bzion-config.php
cp bzion-config-example.php $FILE

mysql -e "CREATE DATABASE IF NOT EXISTS bzion;" -uroot;
mysql -uroot bzion < DATABASE.sql

sed -i 's/bzion_admin/root/' $FILE
sed -i 's/password//' $FILE
sed -i 's/\$_SERVER\[\"HTTP_HOST\"\]/\"http:\/\/localhost\/bzion\"/' $FILE
sed -i 's/"DEVELOPMENT", FALSE/"DEVELOPMENT", TRUE/' $FILE

echo "error_reporting (E_ALL | E_STRICT | E_DEPRECATED);" >> $FILE
