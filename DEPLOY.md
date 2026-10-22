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
   - `ADMIN_EMAIL` y `ADMIN_PASSWORD` → credenciales del administrador inicial (solo se usan si todavía no existe; ver 3-ter).
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
| `ADMIN_PASSWORD` | contraseña del administrador inicial (mínimo 8 caracteres, con letras y números; solo se usa en el primer deploy) |
| `SEED_DEMO` | `true` |

Para el aforo en tiempo real (ver sección 3-bis) agregar también `BROADCAST_CONNECTION` = `reverb`, `REVERB_APP_ID`
= `gym-control`, `REVERB_APP_KEY` y `REVERB_APP_SECRET` = botón *Generate*, `REVERB_HOST` = `127.0.0.1`,
`REVERB_PORT` = `8080` y `REVERB_SCHEME` = `http`. Sin esas variables el sistema funciona igual y el aforo se
actualiza cada 30 segundos.

La base SQLite vive dentro del contenedor: **se borra en cada deploy o reinicio** y vuelve a cargarse con los datos de demostración. Sirve para mostrar el sistema, no para usarlo en el gimnasio.

## 2-ter. Demo gratuita con datos persistentes (MySQL externo en Aiven)
Es la configuración de la entrega: el web service gratuito de la sección 2-bis, pero con la base en un MySQL 8
administrado y gratuito de Aiven, así los datos sobreviven a cada deploy (RNF03).

1. En https://console.aiven.io, **Create service → MySQL → plan Free**. Esperar el estado *Running*.
2. En **Overview → Connection information** están el host, el puerto, el usuario (`avnadmin`), la contraseña, la
   base (`defaultdb`) y el **CA certificate**.
3. En el servicio de Render, cambiar `DB_CONNECTION` a `mysql` y agregar:

| Name | Value |
|---|---|
| `DB_HOST` | el host de Aiven |
| `DB_PORT` | el puerto de Aiven |
| `DB_DATABASE` | `defaultdb` |
| `DB_USERNAME` | `avnadmin` |
| `DB_PASSWORD` | la contraseña de Aiven (cargarla directo en Render, nunca en el repositorio) |
| `DB_SSL_CA_PEM` | el texto completo del CA certificate, desde `-----BEGIN CERTIFICATE-----` hasta `-----END CERTIFICATE-----` |
| `SEED_DEMO` | `true` la primera vez; el seeder no duplica datos si ya hay socios |

**Por qué el certificado.** Aiven solo acepta conexiones cifradas con TLS. `docker/entrypoint.sh` guarda
`DB_SSL_CA_PEM` en `storage/db-ca.pem` y lo pasa a PDO como `MYSQL_ATTR_SSL_CA`. Con ese certificado la aplicación
además **verifica** que habla con el servidor de Aiven y no con un intermediario: con otro certificado la conexión
se rechaza (*Cannot connect to MySQL using SSL*).

**Verificación.** En el log del deploy aparecen las migraciones sin el mensaje *Esperando a MySQL*. Cargar un
socio de prueba, hacer *Manual Deploy* en Render y comprobar que el socio sigue ahí.

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

## 3-ter. Contraseñas del personal (HU30)
El seeder crea el administrador inicial **una sola vez**: si ya existe, no lo toca, así que cambiar
`ADMIN_PASSWORD` en Render después del primer deploy no cambia nada. En producción (`APP_ENV=production`) no hay
contraseña por defecto: sin `ADMIN_PASSWORD` no se crea ningún administrador.

- **Cambiar la propia contraseña:** iniciar sesión → clic en el nombre arriba a la derecha → *Mi contraseña*. Pide la
  actual y cierra las demás sesiones abiertas con ese usuario.
- **Crear o restablecer cuentas del personal:** el administrador lo hace en *Operadores* (alta, edición, nueva
  contraseña, desactivar).
- **Si nadie puede entrar:** en Render → servicio → *Shell*, ejecutar
  `php artisan gym:clave-operador correo@del-admin` y escribir la contraseña nueva dos veces (no se muestra en
  pantalla y no queda en el repositorio ni en las variables).

Si la base se sembró con `SEED_DEMO=true`, los usuarios `recepcion@gymcontrol.test`, `profesor@gymcontrol.test` y `terminal@gymcontrol.test`
tienen contraseñas públicas (están en el README): en producción, cambiarlas o desactivarlos desde *Operadores*.

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
Blueprint (sección 2) o un MySQL administrado externo (sección 2-ter). Con Aiven, el respaldo se hace desde
cualquier equipo con el cliente de MySQL: `mysqldump -h <host> -P <puerto> -u avnadmin -p --ssl-ca=ca.pem
--single-transaction --set-gtid-purged=OFF defaultdb > respaldo.sql`.

## 5. Notas y alternativas
- Si el disco de MySQL diera error de inicialización, revisar los *logs* de `gym-control-db`; el `Dockerfile` de MySQL usa el subdirectorio `/var/lib/mysql/data` justamente para evitar el directorio `lost+found` del disco.
- Alternativa sin servicio MySQL propio: usar un MySQL administrado externo, como en la sección 2-ter (eliminar `gym-control-db` del Blueprint).
- Con un solo disco, el servicio web no puede escalar a más de una instancia (alcanza para un gimnasio).
- La cámara del navegador para escanear QR requiere HTTPS (Render lo provee) y un navegador con `BarcodeDetector` (Chrome/Edge en Android o escritorio). Un lector USB de QR funciona en cualquier navegador porque "tipea" el código y presiona Enter.
