# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "192.168.22.2"

  config.vm.synced_folder "./", "/vagrant", id: "vagrant-root",
    owner: "www-data",
    group: "www-data",
    mount_options: ["dmode=777,fmode=777"]

  config.vm.provision "shell", inline: <<-SHELL
    export DEBIAN_FRONTEND=noninteractive
    sudo apt-get update
    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password mysqlsecretpassword'
    sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password mysqlsecretpassword'
    sudo apt-get install -y nginx php5-curl php5-fpm php5-cli mysql-server libssh2-php beanstalkd php5-mysqlnd php5-mcrypt beanstalkd

    mysql -uroot -pmysqlsecretpassword -e 'CREATE DATABASE IF NOT EXISTS fodor;'
    mysql -uroot -pmysqlsecretpassword -e 'GRANT ALL ON fodor.* to "fodor"@"localhost" IDENTIFIED BY "fodorsecret";'

    sudo curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    cd /vagrant/

    mkdir storage/publickeys
    mkdir storage/privatekeys
    chmod g+wr storage/publickeys
    chmod g+wr storage/privatekeys

    /usr/local/bin/composer install
    php artisan migrate

    sudo php5enmod mcrypt

    cat << EOF > /etc/supervisor/conf.d/fodor-provisioner-worker.conf 
	[program:fodor-provision-worker]
	process_name=%(program_name)s_%(process_num)02d
	command=php /vagrant/artisan queue:work --sleep=1 --tries=10 --delay=0 --daemon
	autostart=true
	autorestart=true
	user=vagrant
	numprocs=4
	redirect_stderr=true
	stdout_logfile=/vagrant/storage/logs/%(program_name)s_%(process_num)02d.log
EOF

    cat << EOF > /etc/nginx/sites-enabled/fodor.conf
server {
        listen   80 default_server;

        root /vagrant/public/;
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
                fastcgi_split_path_info         ^(.+\.php)(.*)$;
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

  SHELL
end
