#!/bin/bash

if [ "$BASE" == "" -a "$PROJECT_BASE_DIR" == "" ]; then
    echo "Need configure"
    echo "  export PROJECT_BASE_DIR=<project base dir>"
    BINDIR=$(cd $(dirname $0) && pwd)
    echo "  export PATH=$BINDIR:\$PATH"
    exit 1
fi

CMD=`dirname $0`/cmd.php
/usr/bin/php $CMD $@
