# Guía de despliegue en Render

Requisitos: cuenta en GitHub y en Render (https://render.com). Los **discos persistentes** (necesarios para MySQL y los certificados) requieren planes pagos (*Starter*).

## 1. Subir el proyecto a GitHub
```bash
cd gym-control
composer install            # genera composer.lock (commitearlo: hace el build reproducible)
git init && git add . && git commit -m "Gym Control - TFI"
git branch -M main
git remote add origin https://github.com/<usuario>/gym-control.git
git push -u origin main
```
(Opcional: `npm install` para generar `package-lock.json` y commitearlo.)

## 2. Crear los servicios con el Blueprint
1. Render → **New → Blueprint** → elegir el repositorio. Render lee `render.yaml` y crea:
   - `gym-control` (web, Docker) con disco de 1 GB para los certificados.
   - `gym-control-db` (servicio privado, MySQL 8) con disco de 5 GB.
2. Cuando lo pida, completar las variables marcadas `sync: false`:
   - `APP_URL` → la URL pública, p. ej. `https://gym-control.onrender.com` (se conoce al crear el servicio; se puede editar después).
   - `ADMIN_EMAIL` y `ADMIN_PASSWORD` → credenciales del administrador inicial.
3. Esperar el primer *deploy*. El contenedor, al iniciar, espera a MySQL, ejecuta `migrate` y `db:seed` (crea los planes Básica/Media/Premium y el administrador) y deja la aplicación escuchando en `$PORT`.
4. Abrir la URL, iniciar sesión con el administrador y crear los recepcionistas desde *Operadores*.

Para la presentación puede ponerse `SEED_DEMO=true` (socios de ejemplo) y volver a `false` después.

## 2-bis. Alternativa gratuita para la demo (un solo Web Service, sin MySQL)
Render → **New → Web Service** → repositorio GymControl → *Language: Docker* → plan *Free*. En *Environment Variables* cargar:

| Name | Value |
|---|---|
| `APP_KEY` | botón *Generate* de Render |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | la URL del servicio, p. ej. `https://gym-control.onrender.com` |
| `APP_TIMEZONE` | `America/Argentina/Buenos_Aires` |
| `APP_LOCALE` | `es` |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `sqlite` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `ADMIN_EMAIL` | correo del administrador |
| `ADMIN_PASSWORD` | contraseña del administrador (mínimo 8 caracteres) |
| `SEED_DEMO` | `true` |

Para el aforo en tiempo real (ver sección 3-bis) agregar también `BROADCAST_CONNECTION` = `reverb`, `REVERB_APP_ID`
= `gym-control`, `REVERB_APP_KEY` y `REVERB_APP_SECRET` = botón *Generate*, `REVERB_HOST` = `127.0.0.1`,
`REVERB_PORT` = `8080` y `REVERB_SCHEME` = `http`. Sin esas variables el sistema funciona igual y el aforo se
actualiza cada 30 segundos.

La base SQLite vive dentro del contenedor: **se borra en cada deploy o reinicio** y vuelve a cargarse con los datos de demostración. Sirve para mostrar el sistema, no para usarlo en el gimnasio.

## 3. Verificación
- `https://<tu-app>.onrender.com/up` responde 200.
- Crear un socio → cargar apto → registrar pago → probar el PIN en *Pantalla de acceso*.

## 3-bis. Aforo en tiempo real (Reverb)
- **Cómo funciona.** Laravel Reverb (servidor de WebSockets) corre dentro del mismo contenedor web, en
  `127.0.0.1:8080`. Lo inicia `docker/entrypoint.sh` cuando `BROADCAST_CONNECTION=reverb`, y lo reinicia si se
  cae. Render expone un solo puerto por servicio, así que Apache le pasa las conexiones de `/app/`
  (`docker/reverb-proxy.conf`, módulo `proxy_wstunnel`). El navegador se conecta a `wss://<tu-app>.onrender.com/app/...`
  sin puertos extra.
- **Costo.** No suma servicios: corre en el mismo web service.
- **Blueprint.** `render.yaml` ya trae las variables: la clave y el secreto se generan solos. Si el servicio se
  creó antes, abrir el Blueprint en Render y aplicar los cambios (*Sync*) para que se agreguen.
- **Verificación.** En el log del servicio aparece `Starting server on 127.0.0.1:8080`. Con el portal abierto en
  un navegador, registrar un ingreso en la *Pantalla de acceso*: el número del aforo cambia en el acto, sin
  esperar los 30 segundos.
- **Si falla.** Si Reverb no responde, el ingreso se registra igual: el error queda en el log y el aforo se
  sigue actualizando cada 30 s con `wire:poll`.

## 4. Respaldos (RNF03)
Un respaldo es una copia de la base en un archivo `.sql` con las sentencias que la vuelven a crear (`CREATE TABLE`
e `INSERT`). Solo sirve si se probó restaurarlo, por eso esta sección trae los dos comandos y la prueba.

**Crear el respaldo.** Desde el *Shell* del servicio de base de datos:
```bash
mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines --triggers gym_control \
  > /var/lib/mysql/respaldo-$(date +%F).sql
```
`--single-transaction` toma una foto consistente de las tablas InnoDB sin bloquear el sistema mientras se usa, y
`--routines --triggers` incluye procedimientos y disparadores si se agregan en el futuro. Después, descargar el
archivo a otra ubicación: un respaldo que queda en el mismo disco se pierde junto con él.

**Restaurar.** Sobre una base vacía:
```bash
mysql -u root -p"$MYSQL_ROOT_PASSWORD" gym_control < /var/lib/mysql/respaldo-AAAA-MM-DD.sql
```

**Frecuencia recomendada.** Uno por día al cierre del gimnasio y antes de cada deploy con migraciones nuevas.

**Prueba realizada (22/10/2026, MySQL 8.0.46 con los datos de demostración).**

| Paso | Resultado |
|---|---|
| Conteo antes | socios 5, pagos 5, asistencias 60, aptos 4, membresías 3, operadores 3; suma de importes $ 170.000 |
| `mysqldump` | 0,17 s; archivo de 37 KB con 21 `CREATE TABLE` |
| Pérdida simulada | `DROP DATABASE gym_control`: la aplicación responde *Base table or view not found* |
| Restauración | 0,42 s |
| Conteo después | Idéntico al de antes, incluida la suma de importes; `php artisan migrate:status` muestra todas las migraciones aplicadas |

Los certificados de aptos físicos no están en la base: quedan en el disco `certificados` del servicio web y se
respaldan copiando esa carpeta (`storage/app/certificados`).

**Limitación de la demo gratuita (sección 2-bis).** Con SQLite dentro del contenedor no hay nada que respaldar:
la base se borra en cada deploy. Para que el gimnasio use el sistema con datos reales hace falta la base MySQL del
Blueprint (sección 2) o un MySQL administrado externo (sección 5).

## 5. Notas y alternativas
- Si el disco de MySQL diera error de inicialización, revisar los *logs* de `gym-control-db`; el `Dockerfile` de MySQL usa el subdirectorio `/var/lib/mysql/data` justamente para evitar el directorio `lost+found` del disco.
- Alternativa sin servicio MySQL propio: usar un MySQL administrado externo (por ejemplo Aiven o similares) y definir `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` en el servicio web (eliminar `gym-control-db` del Blueprint).
- Con un solo disco, el servicio web no puede escalar a más de una instancia (alcanza para un gimnasio).
- La cámara del navegador para escanear QR requiere HTTPS (Render lo provee) y un navegador con `BarcodeDetector` (Chrome/Edge en Android o escritorio). Un lector USB de QR funciona en cualquier navegador porque "tipea" el código y presiona Enter.
