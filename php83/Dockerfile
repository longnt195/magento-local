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
FROM php:8.3-fpm

COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN apt-get update && apt-get install -y \
        vim \
        git \
        curl \
        wget \
        ca-certificates

# PHP extensions
ENV PHP_EXTENSIONS bcmath bz2 calendar exif gd gettext intl mysqli opcache pdo_mysql redis soap sockets sodium sysvmsg sysvsem sysvshm xsl zip pcntl ftp
RUN apt-get update \
  && apt-get upgrade -y \
  && apt-get install -y --no-install-recommends \
  apt-utils \
  sendmail-bin \
  sendmail \
  sudo \
  iproute2 \
  git \
  gnupg2 \
  ca-certificates \
  lsb-release \
  libbz2-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libfreetype6-dev \
  libgmp-dev \
  libgpgme11-dev \
  libicu-dev \
  libldap2-dev \
  libpcre2-dev \
  libpspell-dev \
  libtidy-dev \
  libxslt1-dev \
  libyaml-dev \
  libzip-dev \
  zip \
  python3-yaml \
  && rm -rf /var/lib/apt/lists/*

# Configure the gd library
RUN docker-php-ext-configure \
  gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-configure \
  opcache --enable-opcache

# Install required PHP extensions
RUN docker-php-ext-install -j$(nproc) \
  bcmath \
  bz2 \
  calendar \
  exif \
  gd \
  gettext \
  gmp \
  intl \
  mysqli \
  opcache \
  pdo_mysql \
  pspell \
  shmop \
  soap \
  sockets \
  sysvmsg \
  sysvsem \
  sysvshm \
  tidy \
  xsl \
  zip \
  pcntl \
  ftp

RUN pecl install -o -f \
  gnupg \
  mailparse \
  msgpack \
  oauth \
  pcov \
  raphf \
  redis \
  xdebug-3.3.2 \
  xmlrpc-1.0.0RC3 \
  yaml

RUN docker-php-ext-enable xdebug

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