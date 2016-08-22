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

Please make sure you run sql server first

    wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key|sudo apt-key add -
    echo deb http://v.s.cz/ stable main > /etc/apt/sources.list.d/ease.list
    aptitude update
    aptitude install icinga-editor
    

Sorry
=====

  * We support only Icinga 1.x configuration yet ...
  * English translation is under progress. But some pages localization is still in CZECH language.

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

