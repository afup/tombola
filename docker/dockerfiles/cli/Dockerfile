FROM php:7.0.14

RUN echo "date.timezone=Europe/Paris" >> "/usr/local/etc/php/php.ini"

# pour installation de composer
RUN apt-get update && apt-get install -y wget

# pour récupération des dépots avec composer
RUN apt-get update && apt-get install -y zlib1g-dev
RUN docker-php-ext-install zip
RUN apt-get update && apt-get install -y git

# Install local user mapped to the host user uid
ARG uid=1008
ARG gid=1008

RUN groupadd -g ${gid} localUser && \
    useradd -u ${uid} -g ${gid} -m -s /bin/bash localUser && \
    usermod -a -G www-data localUser

