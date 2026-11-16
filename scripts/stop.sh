#!/bin/bash

if (( $# == 0 )); then
    sudo /opt/lampp/lampp stop
    exit 0
fi

if [[ "$1" == "apache2" || "$1" == "apache" ]]; then
    sudo /opt/lampp/lampp stopapache
    exit 0
elif [[ "$1" == "mysql" ]]; then
    sudo /opt/lampp/lampp stopmysql
    exit 0
fi