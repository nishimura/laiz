#!/bin/bash

BINDIR=$(cd $(dirname $0) && pwd)
echo "run export PATH=$BINDIR:$PATH"
export PATH=$BINDIR:$PATH
echo "done."
