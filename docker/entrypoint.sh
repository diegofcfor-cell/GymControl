#!/bin/sh
set -e
cd /var/www/html

# Render entrega un puerto en $PORT (por defecto 10000)
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# APP_KEY: Laravel espera "base64:<32 bytes>". Si la plataforma entrega solo el valor en base64, se agrega el prefijo.
if [ -z "$APP_KEY" ]; then
  echo "ERROR: falta la variable APP_KEY" >&2
  exit 1
fi
case "$APP_KEY" in
  base64:*) ;;
  *)
    if [ "${#APP_KEY}" -ne 32 ]; then
      APP_KEY="base64:$APP_KEY"
      export APP_KEY
    fi
    ;;
esac

mkdir -p storage/app/certificados storage/framework/cache storage/framework/sessions storage/framework/views

# Sin MySQL (por ejemplo, un único Web Service gratuito de Render) se usa SQLite dentro de storage/
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  DB_DATABASE="${DB_DATABASE:-/var/www/html/storage/database.sqlite}"
  export DB_DATABASE
  touch "$DB_DATABASE"
fi

# Esperar a la base de datos (hasta ~60 s) y migrar
if [ "${DB_CONNECTION:-sqlite}" = "mysql" ]; then
  i=0
  until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".(getenv("DB_PORT")?:3306).";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); exit(0);} catch (Throwable $e) { exit(1);} '; do
    i=$((i+1)); [ $i -ge 30 ] && echo "No se pudo conectar a MySQL" >&2 && exit 1
    echo "Esperando a MySQL ($i/30)..."; sleep 2
  done
fi

php artisan migrate --force
php artisan db:seed --force      # idempotente: crea planes y administrador si no existen
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Al final, para que Apache (www-data) pueda escribir también lo que crearon los comandos anteriores
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Servidor de WebSockets (Reverb) para el aforo en tiempo real, solo si está activado. Escucha solo dentro del
# contenedor; Apache le pasa /app (ver docker/reverb-proxy.conf). Si se cae, se vuelve a iniciar a los 5 s.
if [ "${BROADCAST_CONNECTION:-}" = "reverb" ]; then
  ( while true; do php artisan reverb:start --host=127.0.0.1 --port=8080; sleep 5; done ) &
fi

# Tareas programadas (vencimientos de aptos) cada minuto, en segundo plano
( while true; do php artisan schedule:run >> /dev/null 2>&1; sleep 60; done ) &

exec "$@"
