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

# Run PHPUnit and save its return code
vendor/phpunit/phpunit/phpunit $COVERAGE_TYPE
PHPUNIT=$?

# Same for behat
vendor/behat/behat/bin/behat
BEHAT=$?

# Find all PHP files on the root directory which do not start with "bzion"
FILES="`find . -maxdepth 1 -iname '*.php' -and ! -iname 'bzion*' | sort`"

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

if [[ $BEHAT -ne 0 ]]; then
    exit 3
fi
