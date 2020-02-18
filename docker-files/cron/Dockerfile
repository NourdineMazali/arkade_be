FROM php:7.3-fpm


RUN apt-get update \
&& apt-get install -y \
           cron

#ADD /docker/php-fpm/php.ini /usr/local/etc/php/conf.d/php.ini

ADD /docker-files/cron/scheduler /etc/cron.d/scheduler

RUN crontab /etc/cron.d/scheduler
