#!/usr/bin/env bash

phpunit --coverage-text --bootstrap bzion-load.php --colors tests
PHPUNIT=$?

FILES="`find . -maxdepth 1 -iname '*.php' -and ! -iname 'bzion*' | sort`"

echo -e "\n\n"

tests/testFile.sh $FILES
FILETEST=$?


if [[ $PHPUNIT -ne 0 ]]; then
    exit 1
fi

if [[ $FILETEST -ne 0 ]]; then
    exit 2
fi