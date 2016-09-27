Icinga Configurator
===================

Gui for generating Icinga configuration files and much more...

[![Source Code](http://img.shields.io/badge/source-Vitexus/icinga_configurator-blue.svg?style=flat-square)](https://github.com/Vitexus/icinga_configurator)
[![Latest Version](https://img.shields.io/github/release/Vitexus/icinga_configurator.svg?style=flat-square)](https://github.com/Vitexus/icinga_configurator/releases)
[![Software License](https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square)](https://github.com/Vitexus/icinga_configurator/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/Vitexus/icinga_configurator/master.svg?style=flat-square)](https://travis-ci.org/Vitexus/icinga_configurator)
[![Coverage Status](https://img.shields.io/coveralls/Vitexus/icinga_configurator/master.svg?style=flat-square)](https://coveralls.io/r/Vitexus/icinga_configurator?branch=master)

Features
--------

 * Generate configuration for multiplete users
 * Provide deploy scripts for auto configure NSClient++ 
 * Checked Services presets - to apply on host or hostgroup
 * Scannig tcp ports for known services
 * Outdated sensors overview
 * Recursive import configuration form icinga/nagios config files
 * Export configurations to another instance Icinga-editor using HTTP
 * Network topology map
 * Tool to watch traceroute ping 
 * Automatic Downtime Schedule for devices every night down (Host is server switch)
 * Host icon downloader

Instalation
-----------

### Debian 8 

Please make sure you run sql server first

    wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key|sudo apt-key add -
    echo deb http://v.s.cz/ stable main > /etc/apt/sources.list.d/ease.list
    aptitude update
    aptitude install icinga-editor

### Centos 6.8    

Make sure you have icinga installed. ( http://packages.icinga.org/epel/ )

```
rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-6.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el6/latest.rpm
yum install php56w php56w-opcache php56w-pdo php56w-intl php56w-mysqlnd php56w-pear php56w-gd
pear install Mail
pear install Mail_mime
service httpd restart
service mysqld start
mysqladmin create icinga_editor
echo "GRANT ALL ON icinga_editor.* to 'icinga_editor'@'localhost' IDENTIFIED BY 'LojAstod9';" | mysql -u root -p
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
curl --silent --location https://rpm.nodesource.com/setup_6.x | bash -
yum install -y nodejs
cd /opt/
npm install bootstrap@3
echo "Alias /javascript/twitter-bootstrap /opt/node_modules/bootstrap/dist" > /etc/httpd/conf.d/twitterbootstrap.conf
yum install jquery
echo "Alias /javascript/jquery /usr/share/javascript/jquery/latest" > /etc/httpd/conf.d/jquery.conf
git clone https://github.com/nostalgiaz/bootstrap-switch.git
cp bootstrap-switch/dist/js/bootstrap-switch.js node_modules/bootstrap/dist/js/
cp bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.css node_modules/bootstrap/dist/css/
git clone https://github.com/VitexSoftware/Icinga-Editor.git
cp Icinga-Editor/debian/conf/icinga-editor.conf /etc/httpd/conf.d/
cp /opt/Icinga-Editor/debian/conf/icinga-editor /etc/sudoers.d
sed -i -e 's/\/usr\/share\/icinga-editor/\/opt\/Icinga-Editor\/src\//g' /etc/httpd/conf.d/icinga-editor.conf
mysql -u root icinga_editor < /opt/Icinga-Editor/debian/sql/install/mysql
mkdir -p /etc/icinga/generated/
chown apache /etc/icinga/generated/ -R
chcon -t httpd_sys_rw_content_t /etc/icinga/generated -R
mkdir -p /usr/share/icinga/htdocs/
ln -s /usr/share/icinga/images /usr/share/icinga/htdocs/images/
mkdir -p /usr/share/icinga/htdocs/images/logos/custom/
chcon -t httpd_sys_rw_content_t /usr/share/icinga/htdocs/images/logos/custom/ -R
chown apache /usr/share/icinga/htdocs//images/logos/custom/ -R
chcon -t httpd_sys_rw_content_t /usr/share/icinga/htdocs/images/logos/ -R
cp Icinga-Editor/bin/toicmdfile.sh /usr/bin
sed -i -e 's/..\/includes/\..\/src\/includes/g' Icinga-Editor/bin/iecfgimporter.php
cd Icinga-Editor/bin
./iecfgimporter.php
ln -s /usr/share/icinga/images/logos/ /opt/Icinga-Editor/src/logos
```



Sorry
=====

  * We support only Icinga 1.x configuration yet ...
  * English translation is under progress. But some pages localization is still in CZECH language.

See in Action
=============


Production Site
---------------

Latest version of Debian package is installed here: http://v.s.cz/icinga-editor/

 * Production site with SMS and XMPP (jabber) notifications Enabled. 
 * Twitter inc. recognize our notifications as spam thus Twitter notifications are
depricated now.


Screenshots
-----------

Application Dashboard:
![Dashboard](https://raw.githubusercontent.com/VitexSoftware/Icinga-Editor/master/doc/dashboard.png)

Hosts Listing:
![Hosts](https://raw.githubusercontent.com/VitexSoftware/Icinga-Editor/master/doc/hosts.png)

Topology map:
![Map](https://raw.githubusercontent.com/VitexSoftware/Icinga-Editor/master/doc/map.png)

Nrpe config tool:
![NRPE](https://raw.githubusercontent.com/VitexSoftware/Icinga-Editor/master/doc/nrpe.png)

Regenerate all all icinga config files for all users.
![REGENALL](https://raw.githubusercontent.com/VitexSoftware/Icinga-Editor/master/doc/regenall.png)

Script for deployment editation:
![Script](https://raw.githubusercontent.com/VitexSoftware/Icinga-Editor/master/doc/script.png)

Windows pasive configuration script for [NSClient++](https://www.nsclient.org/):
![WinNsca](https://raw.githubusercontent.com/VitexSoftware/Icinga-Editor/master/doc/winnsca.png)

