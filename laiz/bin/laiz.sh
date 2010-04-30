#!/bin/bash

if [ "$BASE" == "" -a "$PROJECT_BASE_DIR" == "" ]; then
    echo "Need configure"
    echo "  export PROJECT_BASE_DIR=<project base dir>"
    echo "  source bin/setpath.sh"
    exit 1
fi

CMD=`dirname $0`/cmd.php
/usr/bin/php $CMD $@
