#!/bin/sh

php artisan horizon &
php artisan horizon &
php artisan horizon &
nginx -g "daemon off;"
