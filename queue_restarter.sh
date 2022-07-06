#!/bin/bash

#kill the previous queue
kill $(ps -ef | grep "artisan horizon" | grep -v "grep" | awk '{print $2}')
kill $(ps -ef | grep "artisan queue:work" | grep -v "grep" | awk '{print $2}')
kill $(ps -ef | grep "artisan schedule:work" | grep -v "grep" | awk '{print $2}')

su - www-data -s /bin/bash -c 'cd /var/www/html; nohup php artisan schedule:work 1>queueout2.txt 2>queueerr2.txt & '
su - www-data -s /bin/bash -c 'cd /var/www/html; nohup php artisan queue:work 1>queueout1.txt 2>queueerr1.txt & '
su - www-data -s /bin/bash -c 'cd /var/www/html; nohup php artisan horizon 1>horizon.txt 2>horizonerr2.txt & '
