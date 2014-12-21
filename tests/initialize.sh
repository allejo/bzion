#!/usr/bin/env bash

COMPOSER_ARGS="--no-interaction"
if [[ $BZION_HHVM -eq 1 ]]; then
   COMPOSER_ARGS="-v"
fi

php composer.phar install $COMPOSER_ARGS --prefer-source

FILE=app/config.yml
cp app/config.example.yml $FILE

mysql -e "CREATE DATABASE IF NOT EXISTS bzion;" -uroot;
mysql -uroot bzion < DATABASE.sql

sed -i 's/development:\s*false/development: force/' $FILE
cat << EOF >> $FILE
    testing:
        host: localhost
        database: bzion
        username: root
        password:
EOF

echo "error_reporting (E_ALL | E_STRICT | E_DEPRECATED);" >> bzion-load.php
