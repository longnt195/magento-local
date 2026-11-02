FROM golang:1.15 AS builder

RUN if [ $(uname -m) = "x86_64" ]; then mailhog_arch="amd64"; else mailhog_arch="arm64"; fi \
    && wget -O mhsendmail.tar.gz https://github.com/mailhog/mhsendmail/archive/refs/tags/v0.2.0.tar.gz \
    && tar -xf mhsendmail.tar.gz \
    && mkdir -p ./src/github.com/mailhog/ \
    && mv ./mhsendmail-0.2.0 ./src/github.com/mailhog/mhsendmail \
    && cd ./src/github.com/mailhog/mhsendmail/ \
    && go get . \
    && GOOS=linux GOARCH=${mailhog_arch} go build -o mhsendmail .

FROM node:14.18.1-alpine AS node
FROM php:8.2-fpm

COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN apt-get update && apt-get install -y \
        vim \
        git \
        curl \
        wget \
        ca-certificates

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions gd intl soap pdo_mysql mysqli bcmath zip apcu xsl sockets json xdebug redis mcrypt opcache imagick calendar

RUN set -eux \
  && export PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;") \
  && export PHP_EXT_DIR=$(php-config --extension-dir) \
  && cd /tmp \
  && if [ $(uname -m) = "x86_64" ]; then ioncube_arch="x86-64"; else ioncube_arch="aarch64"; fi \
  && curl -O https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_${ioncube_arch}.tar.gz \
  && tar zxvf ioncube_loaders_lin_${ioncube_arch}.tar.gz \
  && cp "./ioncube/ioncube_loader_lin_${PHP_VERSION}.so" "${PHP_EXT_DIR}/ioncube_loader_lin_${PHP_VERSION}.so" \
  && echo "zend_extension=${PHP_EXT_DIR}/ioncube_loader_lin_${PHP_VERSION}.so" > /usr/local/etc/php/conf.d/00-ioncube.ini \
  && rm -rf ./ioncube \
  && rm ioncube_loaders_lin_${ioncube_arch}.tar.gz

RUN set -eux \
  && export PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;") \
  && export PHP_EXT_DIR=$(php-config --extension-dir) \
  && cd /tmp \
  && if [ $(uname -m) = "x86_64" ]; then arch="x86-64"; else arch="aarch64"; fi \
  && curl -O https://www.sourceguardian.com/loaders/download/loaders.linux-${arch}.tar.gz \
  && tar zxvf loaders.linux-${arch}.tar.gz \
  && cp "./ixed.${PHP_VERSION}.lin" ${PHP_EXT_DIR} \
  && echo "zend_extension=${PHP_EXT_DIR}/ixed.${PHP_VERSION}.lin" > /usr/local/etc/php/conf.d/00-sourceguardian.ini \
  && rm -rf ./ixed \
  && rm loaders.linux-${arch}.tar.gz

#RUN groupadd -g 1000 www-data && useradd -g 1000 -u 1000 -s /bin/bash www-data
#RUN usermod -aG www-data root

# Install MailHog
COPY --from=builder /go/src/github.com/mailhog/mhsendmail/mhsendmail /usr/local/bin/
RUN chmod +x /usr/local/bin/mhsendmail

RUN docker-php-ext-configure \
  opcache --enable-opcache

# php etc
COPY etc/php-fpm.ini /usr/local/etc/php/conf.d/zz-magento.ini
COPY etc/php-xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug-settings.ini
COPY etc/mail.ini /usr/local/etc/php/conf.d/zz-mail.ini
COPY etc/www.conf /usr/local/etc/php-fpm.d/www.conf

#install Grunt
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
RUN apt-get update && apt-get install -y nodejs

RUN npm install -g grunt-cli

#composer
COPY composer/composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# entrypoint
COPY ./docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# user
RUN chown -Rv www-data:www-data /usr/local /var/www /var/log /usr/local/etc/php/conf.d
RUN mkdir -p /var/www/.ssh \
    && chown -Rv www-data:www-data /var/www/.ssh

USER www-data
