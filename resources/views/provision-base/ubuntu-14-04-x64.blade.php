#!/bin/bash
apt-get update
apt-get install git
mkdir -p {{ $installpath }}

export INSTALLPATH="{{ $installpath }}"
export NAME="{{ $name }}"
export GITURL="https://github.com/${NAME}.git"

cd {{ $installpath }}
git clone $GITURL .