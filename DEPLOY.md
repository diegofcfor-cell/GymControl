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

## 3. Verificación
- `https://<tu-app>.onrender.com/up` responde 200.
- Crear un socio → cargar apto → registrar pago → probar el PIN en *Pantalla de acceso*.

## 4. Respaldos
Desde el *Shell* del servicio de base de datos:
```bash
mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" gym_control > /var/lib/mysql/respaldo-$(date +%F).sql
```
Los certificados quedan en el disco `certificados` del servicio web.

## 5. Notas y alternativas
- Si el disco de MySQL diera error de inicialización, revisar los *logs* de `gym-control-db`; el `Dockerfile` de MySQL usa el subdirectorio `/var/lib/mysql/data` justamente para evitar el directorio `lost+found` del disco.
- Alternativa sin servicio MySQL propio: usar un MySQL administrado externo (por ejemplo Aiven o similares) y definir `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` en el servicio web (eliminar `gym-control-db` del Blueprint).
- Con un solo disco, el servicio web no puede escalar a más de una instancia (alcanza para un gimnasio).
- La cámara del navegador para escanear QR requiere HTTPS (Render lo provee) y un navegador con `BarcodeDetector` (Chrome/Edge en Android o escritorio). Un lector USB de QR funciona en cualquier navegador porque "tipea" el código y presiona Enter.
