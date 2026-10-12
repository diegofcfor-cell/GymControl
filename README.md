# Gym Control

Sistema web de gestión y control de asistencia para gimnasios — **Trabajo Final Integrador**, Tecnicatura Universitaria en Programación (UTN FRRe).
Cliente: Gimnasio Fénix (Formosa). Autores: Cardozo, Diego Fabián · Heretichi, Adriana · López, Gabriela Elvira.

**Stack:** Laravel 12 · PHP 8.3 · Blade + Livewire 3 + Alpine.js · Tailwind CSS 4 · MySQL 8 · DomPDF · Simple QrCode · Docker · Render.

## Funcionalidades

| Módulo | Descripción |
|---|---|
| Autenticación y roles | Login/logout, roles `administrador` y `recepcionista`, middleware `rol:`, operadores activos/inactivos |
| Socios | Alta, edición, baja lógica, búsqueda reactiva (Livewire) por nombre, DNI o PIN, ficha en PDF |
| PIN y QR | PIN único de 6 dígitos + código QR (UUID) por socio; credencial imprimible |
| Membresías | Planes Básica / Media / Premium (pase libre) con precio, duración y tope de horas diarias |
| Pagos | Registro de cobros; actualiza automáticamente el vencimiento; historial filtrable e imprimible |
| Aptos físicos | Carga del certificado (PDF/imagen, almacenado en disco privado) y control de vigencia |
| **Pantalla de acceso** | Ingreso/egreso con PIN o QR (lector USB o cámara). Valida socio activo + cuota vigente + apto vigente. El egreso siempre se permite y calcula el tiempo de permanencia |
| Historial de asistencias | Filtros por socio y fechas, permanencia, informe PDF |
| Dashboard (admin) | Socios al día/vencidos, presentes ahora, ingresos del día/mes, asistencias de 7 días |

Roles: el **administrador** puede todo; el **recepcionista** opera socios, aptos, pagos, acceso e historiales, pero no accede a operadores, membresías, bajas ni dashboard económico.

## Ejecutar en local

### Opción A — Docker (recomendada, incluye MySQL)
```bash
docker compose up --build
# → http://localhost:8000
```
Carga datos de demostración (`SEED_DEMO=true`).

### Opción B — Sin Docker (PHP 8.3, Composer, Node 20+)
```bash
composer setup            # instala dependencias, crea .env, clave, SQLite, migra, siembra y compila los assets
php artisan serve         # → http://localhost:8000
```
(Para MySQL: editar `DB_*` en `.env` y ejecutar `php artisan migrate --seed`.)

### Usuarios de prueba (con `SEED_DEMO=true`)
| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@gymcontrol.test` | `Admin1234!` |
| Recepcionista | `recepcion@gymcontrol.test` | `Recepcion1234!` |

**Cambiar estas contraseñas en producción** (variables `ADMIN_EMAIL` / `ADMIN_PASSWORD`).

### Probar el acceso
1. Iniciar sesión → *Pantalla de acceso*.
2. Ingresar un PIN (se ve en la ficha de cada socio). Primer ingreso = **ingreso**; el mismo PIN otra vez = **egreso** con el tiempo de permanencia.
3. Casos de demostración: Ana Benítez (cuota vencida) y Martín Acosta (sin apto físico) deben ser **denegados**.

## Pruebas automatizadas
```bash
php artisan test
```
Cubren: PIN/QR únicos, ingreso/egreso y permanencia, cuota vencida, apto vencido, egreso siempre permitido, tope de horas diarias, pase libre, límite de intentos, cálculo de vencimientos de pagos y control de roles.

## Despliegue en Render
Ver `DEPLOY.md`.

## Estructura
```
app/Services/AccesoService.php   reglas de ingreso/egreso y permanencia
app/Services/PagoService.php     alta de pagos y cálculo de vencimiento
app/Livewire/                    PantallaAcceso, SociosIndex
app/Http/Controllers/            Socios, Pagos, Aptos, Asistencias, Membresías, Operadores, Dashboard
database/migrations/             modelo ER (users, membresias, socios, pagos, aptos_fisicos, asistencias)
```
