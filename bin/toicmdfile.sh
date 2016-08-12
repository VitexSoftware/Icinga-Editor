#!/bin/sh
CFGFILE="/etc/icinga/icinga.cfg"

if grep  -Fxq "check_external_commands=1" $CFGFILE
then
    CMDFILE=`cat $CFGFILE | grep command_file= | awk -F= '{print $2}'`
    echo $CMDFILE: $1
    echo $1 > $CMDFILE
else
    echo "external commands in $CFGFILE disabled !?!"
fi

