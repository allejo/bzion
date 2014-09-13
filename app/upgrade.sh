#!/usr/bin/env bash
#
# Script to easily upgrade your bzion instance (suitable for production)
#

# Possible directory of composer.phar
DIR="$(dirname $0)/.."

# Command line arguments
if [ $# -eq 0 ]; then
    COMMAND="--no-dev upgrade"
else
    COMMAND=$@
fi

# Find composer's location
if [[ -f "$DIR/composer.phar" ]]; then
    COMPOSER_PATH="$DIR/composer.phar"
else
    COMPOSER_PATH="composer"
fi

# Run the command
$COMPOSER_PATH $COMMAND
