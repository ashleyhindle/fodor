#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
set -e # Error out on any errors

export INSTALLPATH="{{ $installpath }}"
export NAME="{{ $name }}"
export GITURL="https://github.com/${NAME}.git"
export DOMAIN="{{ $domain }}"
export IPV4="{{ $ipv4 }}"

### Custom Inputs
@foreach ($inputs as $name=>$value)
export {{ strtoupper($name) }}={!! escapeshellarg($value) !!}
@endforeach

apt-get -y install git

mkdir -p $INSTALLPATH
cd $INSTALLPATH
git clone --depth=1 $GITURL .
