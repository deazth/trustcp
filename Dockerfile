#FROM php:7.3-apache
FROM harbor.dev.kubix.tm.com.my/trust/trustbpbase:6
COPY . /var/www/html
COPY .env.example /var/www/html/\.env
RUN chown -R www-data:www-data /var/www/html/storage
#RUN docker-php-ext-install xdebug
#RUN docker-php-ext-enable xdebug
RUN cd /var/www/html &&  php artisan key:generate
#RUN cd /var/www/html &&  php artisan passport:keys


#CMD ["./run.sh"]
