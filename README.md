Icinga Configurator
===================

Gui for generating Icinga configuration files and much more...

[![Source Code](http://img.shields.io/badge/source-Vitexus/icinga_configurator-blue.svg?style=flat-square)](https://github.com/Vitexus/icinga_configurator)
[![Latest Version](https://img.shields.io/github/release/Vitexus/icinga_configurator.svg?style=flat-square)](https://github.com/Vitexus/icinga_configurator/releases)
[![Software License](https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square)](https://github.com/Vitexus/icinga_configurator/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/Vitexus/icinga_configurator/master.svg?style=flat-square)](https://travis-ci.org/Vitexus/icinga_configurator)
[![Coverage Status](https://img.shields.io/coveralls/Vitexus/icinga_configurator/master.svg?style=flat-square)](https://coveralls.io/r/Vitexus/icinga_configurator?branch=master)

Instalation:

(Please run sql server first)

    wget -O - http://v.s.cz/info@vitexsoftware.cz.gpg.key|sudo apt-key add -
    echo deb http://v.s.cz/ stable main > /etc/apt/sources.list.d/ease.list
    aptitude update
    aptitude install icinga-editor
    

Sorry: Current Default localization is CZECH. English translation is just finished. 
Sorry: Only for Icinga 1.x yet ...
Please test and start issues in case you have found one.

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

