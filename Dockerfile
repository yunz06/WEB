FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring dom \
    && a2enmod rewrite headers \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html
COPY docker/entrypoint.sh /usr/local/bin/news-hub-entrypoint
RUN chmod +x /usr/local/bin/news-hub-entrypoint \
    && mkdir -p /var/www/html/public/uploads/articles /opt/news-hub-default-uploads \
    && cp -a /var/www/html/public/uploads/articles/. /opt/news-hub-default-uploads/ \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod -R u+rwX,g+rwX /var/www/html/public/uploads

EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/news-hub-entrypoint"]
