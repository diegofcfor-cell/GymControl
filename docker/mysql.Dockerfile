# MySQL 8 para Render (servicio privado con disco persistente en /var/lib/mysql).
# Se usa un subdirectorio de datos para evitar el conflicto con "lost+found" del disco.
FROM mysql:8.0
CMD ["mysqld", "--datadir=/var/lib/mysql/data", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci", "--default-time-zone=-03:00"]
