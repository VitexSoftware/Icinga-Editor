#!/bin/sh
#
# Author: Vítězslav Dvořák <vitex@vitexsoftware.cz>
# Date: 2015-03-01
# License: GNU GPL v2 or later
#
# Host is UP if have any service succesfully checked

set -e

if [ $ICINGA_TOTALHOSTSERVICESOK = '0' ] ; then
    echo "CRITICAL - no fresh service checks results reported"
    exit 3
fi

echo "OK - host up ( $ICINGA_TOTALHOSTSERVICESOK services OK)"
exit 0
