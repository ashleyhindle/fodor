#!/bin/bash
export DEBIAN_FRONTEND=noninteractive

echo 'root:{{ $rootPasswordEscaped }}' | chpasswd

export INSTALLPATH="{{ $installpath }}"
export NAME="{{ $name }}"
export GITURL="https://github.com/${NAME}.git"
export DOMAIN="{{ $domain }}"

apt-get -y update
apt-get -y install git

mkdir -p $INSTALLPATH
cd $INSTALLPATH
git clone --depth 1 $GITURL .
