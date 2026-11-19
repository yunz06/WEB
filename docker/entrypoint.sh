#!/bin/sh
set -eu

PORT="${PORT:-10000}"
case "$PORT" in
  *[!0-9]*|'') echo "PORT must be numeric" >&2; exit 1 ;;
esac

sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s#<VirtualHost \*:80>#<VirtualHost *:${PORT}>#" /etc/apache2/sites-available/000-default.conf

UPLOAD_DIR="/var/www/html/public/uploads/articles"
mkdir -p "$UPLOAD_DIR"
if [ -d /opt/news-hub-default-uploads ]; then
  cp -an /opt/news-hub-default-uploads/. "$UPLOAD_DIR/"
fi
chown -R www-data:www-data "$UPLOAD_DIR"

exec apache2-foreground
