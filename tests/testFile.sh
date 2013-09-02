#!/usr/bin/env bash

# Test a PHP file and see if it produces any errors
#
#
# Example:
#
# tests/testFile.sh matches.php news.php

ERRORLOG="error.log"

GREY="\033[0;37m"
MAGENTA="\033[0;35m"
RED="\033[0;31m"
YELLOW="\033[0;33m"
GREEN="\033[0;32m"
LGREEN="\033[0;92m"
BLUE="\033[0;94m"
CYAN="\033[0;96m"

ERROR=0

NO_COLOR="\033[0;39m"

ERROR_LOG_CONTENTS=""

for file
do
    # Delete error.log if it exists
    [ -f $ERRORLOG ] && rm $ERRORLOG

    php $file > /dev/null 2> $ERRORLOG

    [ -f $ERRORLOG ] || continue

    # Find number of different messages included in the error.log file
    notices=`grep -i notice $ERRORLOG | wc -l`
    warnings=`grep -i warning $ERRORLOG | wc -l`
    fatal=`grep -i fatal $ERRORLOG | wc -l`
    parse=`grep -i parse $ERRORLOG | wc -l`
    strict=`grep -i 'strict\|deprecated' $ERRORLOG | wc -l`

    message=() # Array where parts of the message shown to the user are stored
    color=$GREEN

    if [[ $notices -ne 0 ]]; then
      message+=("$notices notices")
      color=$LGREEN
    fi
    if [[ $warnings -ne 0 ]]; then
      message+=("$warnings warnings")
      color=$YELLOW
    fi
    if [[ $strict -ne 0 ]]; then
      message+=("$strict strict warnings")
      color=$RED
      ERROR=1
    fi
    if [[ $fatal -ne 0 ]]; then
      message+=("$fatal fatal errors")
      color=$MAGENTA
      ERROR=2
    fi
    if [[ $parse -ne 0 ]]; then
      message+=("$parse parse errors")
      color=$MAGENTA
      ERROR=3
    fi

    # If errorlog is not empty, save its contents in a variable
    if [[ `wc -w < $ERRORLOG` -ne 0 ]]; then
        ERROR_LOG_CONTENTS+="\n$BLUE""Error log for file $file:$NO_COLOR\n"
        ERROR_LOG_CONTENTS+=`cat $ERRORLOG`
    fi

    # Implode/join the parts of the message array
    result=$(printf ", %s" "${message[@]}")
    result=${result:2}

    if [ -z "$result" ]; then
        result="No errors found"
    fi

    echo -e "$color$file:\t$result$NO_COLOR"
done

echo -e "$ERROR_LOG_CONTENTS"

exit $ERROR
