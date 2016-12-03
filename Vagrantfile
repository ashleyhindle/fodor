# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"
  #
  # config.vm.box =
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

    sudo apt-get install -y nginx php5-curl php5-fpm php5-cli mysql-server libssh2-php beanstalkd php5-mysqlnd php5-mcrypt beanstalkd redis-server supervisor

    mysql -uroot -pmysqlsecretpassword -e 'CREATE DATABASE IF NOT EXISTS fodor;'
    mysql -uroot -pmysqlsecretpassword -e 'GRANT ALL ON fodor.* to "fodor"@"localhost" IDENTIFIED BY "fodorsecret";'

    sudo curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


    cat << EOF > /etc/supervisor/conf.d/fodor-provisioner-worker.conf
        [program:fodor-provision-worker]
        process_name=%(program_name)s_%(process_num)02d
        command=php /vagrant/artisan queue:listen --timeout=3600 --sleep=1 --tries=15 --delay=0 --queue=default
        autostart=true
        autorestart=true
        user=vagrant
        numprocs=4
        redirect_stderr=true
        stdout_logfile=/vagrant/storage/logs/%(program_name)s_%(process_num)02d.log
EOF



    cd /vagrant/

    mkdir storage/publickeys
    mkdir storage/privatekeys
    chmod g+wr storage/publickeys
    chmod g+wr storage/privatekeys

    /usr/local/bin/composer install
    php artisan migrate

    sudo php5enmod mcrypt

    # TODO: This cat fails, presumably because supervisor isn't installed - and I shouldn't stop writing code
    # Willy nilly
    cat << EOF > /etc/supervisor/conf.d/fodor-provisioner-worker.conf 
	[program:fodor-provision-worker]
	process_name=%(program_name)s_%(process_num)02d
	command=php /vagrant/artisan queue:listen --timeout=3600 --sleep=1 --tries=15 --delay=0 --queue=default
	autostart=true
	autorestart=true
	user=vagrant
	numprocs=4
	redirect_stderr=true
	stdout_logfile=/vagrant/storage/logs/%(program_name)s_%(process_num)02d.log
EOF

    rm /etc/nginx/sites-enabled/default
    cp /vagrant/provisioner/nginx-vhost.conf /etc/nginx/sites-enabled/fodor.conf

    sudo service nginx restart
    sudo service php5-fpm restart

  SHELL
end
