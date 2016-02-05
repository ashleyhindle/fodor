#!/bin/bash

export MYSQL_ROOT_PASSWORD=oAoAoAoA
export INSTALLPATH=/var/www/fodor.xyz/
export GITURL="https://github.com/ashleyhindle/fodor.git"

apt-get update
apt-get install -y git
mkdir -p $INSTALLPATH

cd $INSTALLPATH
git clone --depth 1 $GITURL .

export DEBIAN_FRONTEND=noninteractive
useradd -m -s /bin/bash fodor

apt-get -y update
apt-get install -y sudo git nginx php5-curl php5-fpm php5-cli mysql-server libssh2-php beanstalkd php5-mysqlnd php5-mcrypt

debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_ROOT_PASSWORD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_ROOT_PASSWORD"

mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE IF NOT EXISTS fodor;"
mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "GRANT ALL ON fodor.* to 'fodor'@'localhost' IDENTIFIED BY '$MYSQL_FODOR_PASSWORD';"

sudo curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

rm /etc/nginx/sites-enabled/default

chown -R www-data:www-data $INSTALLPATH
chmod -R g+wr $INSTALLPATH

cd $INSTALLPATH

mkdir storage/publickeys
mkdir storage/privatekeys
chmod g+wr storage/publickeys
chmod g+wr storage/privatekeys

/usr/local/bin/composer install
./artisan migrate

sudo php5enmod mcrypt

cat << EOF > /etc/nginx/sites-enabled/fodor.conf
server {
	listen   80 default_server;

	root $INSTALLPATH/public/;
	index index.php;

	location / {
# URLs to attempt, including pretty ones.
		try_files \$uri \$uri/ /index.php?\$query_string;
	}

# Remove trailing slash to please routing system.
	if (!-d \$request_filename) {
		rewrite     ^/(.+)/\$ /\$1 permanent;
	}

# PHP FPM configuration.
	location ~* \.php\$ {
		fastcgi_pass                    unix:/var/run/php5-fpm.sock;
		fastcgi_index                   index.php;
		fastcgi_split_path_info         ^(.+\.php)(.*)\$;
		include                         /etc/nginx/fastcgi_params;
		fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
	}

# We don't need .ht files with nginx.
	location ~ /\.ht {
		deny all;
	}
}
EOF

sudo service nginx restart
sudo service php5-fpm restart
