# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
	config.vm.box = "ubuntu/bionic64"

	config.vm.network "forwarded_port", guest: 8000, host: 8000

	config.vm.provider "virtualbox" do |vb|
		vb.memory = "2048"
	end

	config.vm.provision "shell", inline: <<-SHELL
		apt-get update -y

		add-apt-repository -y ppa:ondrej/php

		apt-get install -y php5.6-common php5.6-cli php5.6-curl php5.6-zip php5.6-mbstring php5.6-xml php5.6-dom \
			php5.6-xdebug
		apt-get install -y composer

		if grep -Fqvx "xdebug.remote_enable" /etc/php/5.6/mods-available/xdebug.ini; then
			echo "xdebug.remote_enable = on" >> /etc/php/5.6/mods-available/xdebug.ini
			echo "xdebug.remote_connect_back = on" >> /etc/php/5.6/mods-available/xdebug.ini
			echo "xdebug.idekey = application" >> /etc/php/5.6/mods-available/xdebug.ini
			echo "xdebug.remote_autostart = off" >> /etc/php/5.6/mods-available/xdebug.ini
			echo "xdebug.remote_host = 10.0.2.2" >> /etc/php/5.6/mods-available/xdebug.ini
		fi

		apt-get install -y php-apcu
		apt-get remove php7*

		phpenmod apcu
	SHELL

	config.vm.provision "shell", privileged: false, inline: <<-SHELL
		cd /vagrant

		composer install
	SHELL
end