#!/bin/sh
# Copyright © 2013-2017 Magento, Inc. All rights reserved.
# See COPYING.txt for license details.

CRONSCRIPT="cron.php"
INSTALLDIR=`echo $0 | sed 's/cron\.sh//g'`"../../"

PHP_BIN=`which php`
if  ! ps auxwww | grep " $CRONSCRIPT" | grep -v grep | grep -v cron.sh 1>/dev/null 2>/dev/null ; then
    $PHP_BIN "$INSTALLDIR""$CRONSCRIPT" &
fi
