#!/usr/bin/env bash

finalStatus=0

function assert() {
    echo "$@"
    echo ""
    "$@"

    local status=$?

    if [ $status -ne 0 ]; then
        echo "error with $1" >&2
    fi

    finalStatus+=$status
}

# validate composer.json
assert composer validate --strictg

# unit tests
# assert bin/phpunit -c etc/phpunit.xml

exit $finalStatus