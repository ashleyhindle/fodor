#!/bin/bash
# $1 is the DATETIME folder that will be created after tar -xzf
export BASEDIR=`dirname $0`
export RELEASE_PATH="${BASEDIR}/${1}"
cd "${BASEDIR}"

mkdir fodor-storage
chown fodor:www-data fodor-storage/

mkdir fodor-storage/{app,framework,logs,privatekeys,publickeys}
mkdir fodor-storage/framework/{cache,sessions,views}
mkdir fodor-storage/app/{publickeys,privatekeys}

chown -R fodor:www-data fodor-storage
chmod -R a-wrx fodor-storage/
chmod -R ug+wr fodor-storage/

# Extract the package
tar -xzf package.tgz # this creates a DATETIME (ymdhis) folder
rm package.tgz

rm -rf "${RELEASE_PATH}/storage"
ln -s "${BASEDIR}/fodor-storage" "${RELEASE_PATH}/storage"

cp /home/fodor/.env "${RELEASE_PATH}/.env"

cd ${RELEASE_PATH}
php artisan migrate --force || exit 1

chown -R fodor:www-data ${RELEASE_PATH}
chmod -R g+wr ${RELEASE_PATH}

rm "${BASEDIR}/current-release"
ln -s "${RELEASE_PATH}" "${BASEDIR}/current-release"
