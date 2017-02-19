FROM php:7.1-cli

RUN docker-php-ext-install bcmath

RUN apt-get update && apt-get install -y curl git

# Redis
RUN cd /etc && git clone --depth=1 -b php7 https://github.com/phpredis/phpredis.git \
    && cd /etc/phpredis \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && touch /usr/local/etc/php/conf.d/ext-redis.ini \
    && echo 'extension=redis.so' >> /usr/local/etc/php/conf.d/ext-redis.ini

RUN curl https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh > /wait.sh && chmod +x /wait.sh