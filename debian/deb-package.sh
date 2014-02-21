#!/bin/bash
rm -rfv /var/tmp/icinga_configurator
cd /var/tmp
git clone git@github.com:Vitexus/EaseFramework.git
cd icinga_configurator

ls -la 

VERSION=`cat version | perl -ne 'chomp; print join(".", splice(@{[split/\./,$_]}, 0, -1), map {++$_} pop @{[split/\./,$_]}), "\n";'`


CHANGES=`git log -n 1 | tail -n+5`
dch -b -v $VERSION --package icinga-editor 

echo $VERSION > ~/Projects/VitexSoftware/IcingaEditor/version

debuild -i -us -uc -b

cd ..
ls *.deb

~/bin/publish-deb-packages.sh
