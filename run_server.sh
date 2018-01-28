#!/bin/bash

#
# Run Mysql Server
#
if ! [ -e /tmp/mysql.sock ]; then
    if [ -e /usr/local/bin/mysql.server ]; then /usr/local/bin/mysql.server start; fi
else
    echo -e "Mysql server is already running."
fi
#
# Run PHP Server
#
php -S 127.0.0.1:8080 ./router.php

exit 0
