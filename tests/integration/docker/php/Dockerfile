FROM php:latest

RUN docker-php-ext-install -j$(nproc) bcmath pcntl
RUN apt-get update && apt-get install -y \
        procps
