#!/bin/bash

if (( $# == 0 )); then
    sudo /opt/lampp/lampp start
    exit 0
fi

if [[ "$1" == "apache2" || "$1" == "apache" ]]; then
    sudo /opt/lampp/lampp startapache
    exit 0
elif [[ "$1" == "mysql" ]]; then
    sudo /opt/lampp/lampp startmysql
    exit 0
fi