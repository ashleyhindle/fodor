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
apt-get install -y sudo git nginx php5-curl php5-fpm php5-cli mysql-server libssh2-php beanstalkd php5-mysqlnd php5-mcrypt beanstalkd

# Secure SSL
openssl dhparam -out /etc/nginx/ssl/dhparam.pem 2048

git clone https://github.com/letsencrypt/letsencrypt /opt/letsencrypt
cat << EOF > /usr/local/etc/le-renew-webroot.ini
rsa-key-size = 4096
email = ashley@fodor.xyz
domains = fodor.xyz
webroot-path = /var/www/fodor.xyz/public/
EOF
curl -L -o /usr/local/sbin/le-renew-webroot https://gist.githubusercontent.com/thisismitch/e1b603165523df66d5cc/raw/fbffbf358e96110d5566f13677d9bd5f4f65794c/le-renew-webroot
chmod +x /usr/local/sbin/le-renew-webroot
echo "30 2 * * 1 /usr/local/sbin/le-renew-webroot >> /var/log/le-renewal.log
" > /etc/cron.d/letsencrypt



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

/usr/local/bin/composer install --prefer-source --no-interaction

./artisan migrate --force

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

cat << EOF > /etc/default/beanstalkd
## Defaults for the beanstalkd init script, /etc/init.d/beanstalkd on
## Debian systems.

BEANSTALKD_LISTEN_ADDR=127.0.0.1
BEANSTALKD_LISTEN_PORT=11300

# You can use BEANSTALKD_EXTRA to pass additional options. See beanstalkd(1)
# for a list of the available options. Uncomment the following line for
# persistent job storage.
BEANSTALKD_EXTRA="-b /var/lib/beanstalkd"
EOF

service beanstalkd restart
service nginx restart
service php5-fpm restart
