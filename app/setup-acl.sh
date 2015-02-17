#!/bin/bash

rm -rf app/cache/*
rm -rf app/logs/*

HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
FOLDERS="app/cache app/logs web/assets/imgs/avatars/"

if hash setfacl 2>/dev/null; then
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX $FOLDERS
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX $FOLDERS
else
    sudo chmod -R +a "$HTTPDUSER allow delete,write,append,file_inherit,directory_inherit" $FOLDERS
    sudo chmod -R +a "`whoami` allow delete,write,append,file_inherit,directory_inherit" $FOLDERS
fi
