#!/usr/bin/env bash
wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key|sudo apt-key add -
echo deb http://v.s.cz/ stable main > /etc/apt/sources.list.d/vitexsoftware.list
echo deb http://debmon.org/debmon debmon-stretch main > /etc/apt/sources.list.d/debmon.list

apt-get update
apt-get install -y apache2
if ! [ -L /var/www ]; then
  rm -rf /var/www
  ln -fs /vagrant /var/www
fi

export DEBIAN_FRONTEND="noninteractive"
apt-get update
apt-get install -y devscripts dpkg-dev dh-systemd
cd /vagrant
make celan
make deb
mkdir -p /vagrant/deb
mv /*.deb /vagrant/deb
cd /vagrant/deb
dpkg-scanpackages . /dev/null | gzip -9c > Packages.gz
echo "deb file:/vagrant/deb ./" > /etc/apt/sources.list.d/local.list
apt-get update
export DEBCONF_DEBUG="developer"
apt-get -y --allow-unauthenticated install icinga-editor

