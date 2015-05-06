#!/usr/bin/env bash

COVERAGE_TYPE="--coverage-text --coverage-clover=coverage.clover"

# Evaluate command-line arguments
while test $# -gt 0; do
    case "$1" in
        -h|--help)
            echo "runTests.sh - run BZIon tests"
            echo " "
            echo "$0 [options]"
            echo " "
            echo "options:"
            echo "-h, --help        show help message"
            echo "-t, --html        save coverage information in HTML format"
            exit 0
            ;;
        -t|--html)
            shift
                COVERAGE_TYPE="--coverage-html coverage-report"
            ;;
    esac
done

if [[ $NO_COVERAGE -eq 1 ]]; then
   COVERAGE_TYPE=""
fi

# Run PHPUnit and save its return code
vendor/phpunit/phpunit/phpunit $COVERAGE_TYPE
PHPUNIT=$?

# Find all PHP files that we manage
FILES="`find . app -maxdepth 1 -iname '*.php' | sort`\
`echo`
`find controllers models src web -iname '*.php' | sort`"

echo -e "\n"

# Test each of those files
tests/testFile.sh $FILES
FILETEST=$?


if [[ $PHPUNIT -ne 0 ]]; then
    exit 1
fi

if [[ $FILETEST -ne 0 ]]; then
    exit 2
fi
