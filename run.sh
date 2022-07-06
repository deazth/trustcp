#!/bin/bash

#kill the previous queue
# kill $(ps -ef | grep "artisan queue:work" | grep -v "grep" | awk '{print $2}')

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan api:cache
php artisan view:cache
chmod 777 bootstrap/*
chmod 777 storage/*
chmod 777 storage/logs/*
chmod 777 storage/app/reports/logs/*
chmod 777 storage/app/reports/logs/trustbp/*


su - www-data -s /bin/bash -c 'cd /var/www/html; nohup php artisan horizon 1>horizon.txt 2>horizonerr2.txt & '
su - www-data -s /bin/bash -c 'cd /var/www/html; nohup php artisan schedule:work 1>queueout2.txt 2>queueerr2.txt & '
# su - www-data -s /bin/bash -c 'cd /var/www/html; nohup php artisan queue:work 1>queueout1.txt 2>queueerr1.txt & '
# su - www-data -s /bin/bash -c 'cd /var/www/html; nohup php artisan queue:work 1>queueout3.txt 2>queueerr3.txt & '

# nohup php artisan queue:work --stop-when-empty 1>queueout.txt 2>queueerr.txt &
