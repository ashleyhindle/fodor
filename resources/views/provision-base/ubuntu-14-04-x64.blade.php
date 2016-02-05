#!/bin/bash
export INSTALLPATH="{{ $installpath }}"
export NAME="{{ $name }}"
export GITURL="https://github.com/${NAME}.git"

apt-get update
apt-get install -y git

mkdir -p $INSTALLPATH
cd $INSTALLPATH
git clone --depth 1 $GITURL .
