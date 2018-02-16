#!/bin/sh

set -e

cd /code; composer install
exec php-fpm
