FROM php:7.1-cli

RUN docker-php-ext-install bcmath

RUN apt-get update && apt-get install -y curl

RUN curl https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh > /wait.sh && chmod +x /wait.sh