#!/usr/bin/env bash

# Test a PHP file and see if it produces any errors
#
#
# Example:
#
# tests/testFile.sh matches.php news.php

# Color codes
GREY="\033[0;37m"
MAGENTA="\033[0;35m"
RED="\033[0;31m"
YELLOW="\033[0;33m"
GREEN="\033[0;32m"
BLUE="\033[0;34m"
CYAN="\033[0;36m"
NO_COLOR="\033[0;39m"

# The return code of the script
ERROR=0

# A string containing PHP's output which includes all the errors found
ERROR_LOG=""

for file
do
    ERRORS=`php $file 2>&1 > /dev/null`

    # Find number of different messages included PHP's output
    notices=`echo $ERRORS | grep -i notice | wc -l`
    warnings=`echo $ERRORS | grep -i warning | wc -l`
    fatal=`echo $ERRORS | grep -i fatal | wc -l`
    parse=`echo $ERRORS | grep -i parse | wc -l`
    strict=`echo $ERRORS | grep -i 'strict\|deprecated' | wc -l`

    message=() # Array where parts of the message shown to the user are stored
    color=$GREEN

    if [[ $notices -ne 0 ]]; then
      message+=("$notices notices")
    fi
    if [[ $warnings -ne 0 ]]; then
      message+=("$warnings warnings")
      color=$YELLOW
    fi
    if [[ $strict -ne 0 ]]; then
      message+=("$strict strict warnings")
      color=$MAGENTA
      ERROR=1
    fi
    if [[ $fatal -ne 0 ]]; then
      message+=("$fatal fatal errors")
      color=$RED
      ERROR=2
    fi
    if [[ $parse -ne 0 ]]; then
      message+=("$parse parse errors")
      color=$RED
      ERROR=3
    fi

    # If $ERRORS is not empty, save its contents in $ERROR_LOG_CONTENTS
    if [[ `echo $ERRORS | wc -w` -ne 0 ]]; then
        ERROR_LOG_CONTENTS+="\n$BLUE""Error log for file $file:$NO_COLOR\n"
        ERROR_LOG_CONTENTS+="$ERRORS"
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
