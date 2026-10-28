# Gym Control

Sistema web de gestión y control de asistencia para gimnasios — **Trabajo Final Integrador**, Tecnicatura Universitaria en Programación (UTN FRRe).
Cliente:  Autor: Cardozo, Diego Fabián

**Stack:** Laravel 12 · PHP 8.3 · Blade + Livewire 3 + Alpine.js · Tailwind CSS 4 · MySQL 8 · DomPDF · Simple QrCode · Docker · Render.

## Funcionalidades

| Módulo | Descripción |
|---|---|
| Autenticación y roles | Login/logout, roles `administrador`, `recepcionista` y `profesor`, middleware `rol:`, operadores activos/inactivos |
| Socios | Alta, edición, baja lógica, búsqueda reactiva (Livewire) por nombre, DNI o PIN, ficha en PDF |
| PIN y QR | PIN único de 6 dígitos + código QR (UUID) por socio; credencial imprimible |
| Membresías | Planes Básica / Media / Premium (pase libre) con precio, duración y tope de horas diarias |
| Pagos | Registro de cobros; actualiza automáticamente el vencimiento; historial filtrable e imprimible |
| Aptos físicos | Carga del certificado (PDF/imagen, almacenado en disco privado) y control de vigencia |
| **Pantalla de acceso** | Ingreso/egreso con PIN o QR (lector USB o cámara). Valida socio activo + cuota vigente + apto vigente. El egreso siempre se permite y calcula el tiempo de permanencia |
| Historial de asistencias | Filtros por socio y fechas, permanencia, informe PDF |
| **Página web pública** | En `/` se muestra la web del gimnasio: presentación, servicios, planes y precios (de las membresías activas), horarios, contacto, aforo en vivo, chatbot flotante y acceso al portal del socio. El administrador edita los textos en *Configuración*. Con sesión de operador, la web muestra el botón *Ir al panel* y el panel tiene el enlace *Ver web* |
| **Portal del socio** | En `/socio`, el socio ingresa con su DNI + PIN (bloqueo tras 5 intentos fallidos por DNI) y consulta su membresía y cuota, el historial completo de asistencias con filtro por fechas, sus pagos con comprobante en PDF y sus constancias médicas (vigencia y descarga del archivo) |
| **Aforo en vivo** | En `/socio/aforo` el socio ve cuántas personas hay ahora respecto de la capacidad del local (verde / amarillo / rojo, se actualiza cada 30 s) y un gráfico de horas pico por día de la semana con el promedio de las últimas 4 semanas. El administrador define la capacidad en *Configuración* |
| **Chatbot de preguntas frecuentes** | En `/socio/preguntas` el socio chatea con un asistente sin IA (sin costo de uso) que busca la respuesta por palabras clave, ignorando tildes y plurales. Planes/precios y "cuánta gente hay" se responden con los datos del sistema. El administrador carga las preguntas en *Preguntas* |
| **Rutinas con profesor** | En `/socio/rutina` el socio pide una rutina (objetivo, nivel, días por semana y comentarios). El profesor ve los pedidos en *Rutinas*, arma los ejercicios por día (series, repeticiones, descanso y notas) y el socio la ve en su portal. Una rutina nueva reemplaza a la anterior y el profesor puede editarla. El rol `profesor` solo accede a *Rutinas* |
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
| Profesor | `profesor@gymcontrol.test` | `Profesor1234!` |

**Cambiar estas contraseñas en producción** (variables `ADMIN_EMAIL` / `ADMIN_PASSWORD`).

### Probar el acceso
1. Iniciar sesión y abrir *Pantalla de acceso* desde el menú.
2. Ingresar un PIN (se ve en la ficha de cada socio). Primer ingreso = **ingreso**; el mismo PIN otra vez = **egreso** con el tiempo de permanencia.
3. Casos de demostración: Ana Benítez (cuota vencida) y Martín Acosta (sin apto físico) deben ser **denegados**.

### Probar el portal del socio
1. Abrir `http://localhost:8000/socio`.
2. Ingresar el DNI y el PIN de un socio (el PIN se ve en su ficha).

### Probar las rutinas
El usuario profesor de prueba solo existe si la base se sembró con `SEED_DEMO=true`. En cualquier instalación (por ejemplo Render), el administrador lo crea en *Operadores* eligiendo el rol *Profesor*.

1. En el portal, entrar a *Mi rutina* y enviar un pedido.
2. Iniciar sesión como profesor → *Rutinas* → *Armar rutina* en el pedido.
3. Volver al portal: la rutina aparece agrupada por día.

En una base ya instalada, correr `php artisan migrate` para crear las tablas de rutinas.

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
app/Http/Controllers/            Socios, Pagos, Aptos, Asistencias, Membresías, Operadores, Rutinas, Dashboard
database/migrations/             modelo ER (users, membresias, socios, pagos, aptos_fisicos, asistencias)
```
