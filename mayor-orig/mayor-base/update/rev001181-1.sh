#!/bin/bash

echo -ne "          - A WEB_SERVER_USER környezeti változó felvétele az update.conf állományba ... "
echo -e "WEB_SERVER_USER=www-data\n" >> $BASEDIR/config/update.conf
echo -e "kész.\n"
