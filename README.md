# Gym Control

Sistema web de gestión y control de asistencia para gimnasios — **Trabajo Final Integrador**, Tecnicatura Universitaria en Programación (UTN FRRe).
Cliente: Gimnasio Fénix (Formosa). Autores: Cardozo, Diego Fabián · Heretichi, Adriana · López, Gabriela Elvira.

**Stack:** Laravel 12 · PHP 8.3 · Blade + Livewire 3 + Alpine.js · Tailwind CSS 4 · MySQL 8 · DomPDF · Simple QrCode · Docker · Render.

## Funcionalidades

| Módulo | Descripción |
|---|---|
| Autenticación y roles | Login/logout, roles `administrador`, `recepcionista` y `profesor`, middleware `rol:`, operadores activos/inactivos |
| Socios | Alta, edición, baja lógica, búsqueda reactiva (Livewire) por nombre, DNI o PIN, ficha en PDF |
| PIN y QR | PIN único de 6 dígitos + código QR (UUID) por socio; credencial imprimible |
| Membresías | Planes Básica / Media / Premium (pase libre) con precio, duración y tope de horas diarias |
| Pagos | Registro de cobros; actualiza automáticamente el vencimiento; historial filtrable e imprimible |
| Aptos físicos | Carga del certificado (PDF/imagen, almacenado en disco privado) y control de vigencia. Solo se carga si el socio aceptó el aviso de privacidad |
| **Privacidad (Ley 25.326)** | Aviso de privacidad público en `/privacidad`, enlazado desde la web y el portal. En el alta, el socio acepta el aviso y se guarda la fecha; a los socios anteriores se les registra desde *Editar*. Sin consentimiento no se cargan datos de salud. En *Mis datos* del portal, el socio ve sus datos personales y pide rectificarlos o suprimirlos; el administrador responde en *Datos personales* dentro de los 5 días hábiles del art. 16 (los vencidos se marcan en rojo) |
| **Pantalla de acceso** | Ingreso/egreso con PIN o QR (lector USB o cámara). Valida socio activo + cuota vigente + apto vigente. El egreso siempre se permite y calcula el tiempo de permanencia |
| Historial de asistencias | Filtros por socio y fechas, permanencia, informe PDF |
| **Página web pública** | En `/` se muestra la web del gimnasio: presentación, servicios, planes y precios (de las membresías activas), horarios, contacto, aforo en vivo, chatbot flotante y acceso al portal del socio. El administrador edita los textos en *Configuración*. La portada tiene fondo de fotos en movimiento tomadas de `public/img/portada/` (ver el `LEEME.md` de esa carpeta) y botones de acceso para socios y para el personal. Con sesión de operador, la web muestra el botón *Ir al panel* y el panel tiene el enlace *Ver web* |
| **Portal del socio** | En `/socio`, el socio ingresa con su DNI + PIN (bloqueo tras 5 intentos fallidos por DNI) y consulta su membresía y cuota, el historial completo de asistencias con filtro por fechas, sus pagos con comprobante en PDF y sus constancias médicas (vigencia y descarga del archivo) |
| **Aforo en vivo** | En `/socio/aforo` el socio ve cuántas personas hay ahora respecto de la capacidad del local (verde / amarillo / rojo, se actualiza cada 30 s) y un gráfico de horas pico por día de la semana con el promedio de las últimas 4 semanas. El administrador define la capacidad en *Configuración* |
| **Chatbot de preguntas frecuentes** | En `/socio/preguntas` el socio chatea con un asistente sin IA (sin costo de uso) que busca la respuesta por palabras clave, ignorando tildes y plurales. Planes/precios y "cuánta gente hay" se responden con los datos del sistema. El administrador carga las preguntas en *Preguntas* |
| **Rutinas con profesor** | En `/socio/rutina` el socio pide una rutina (objetivo, nivel, días por semana y comentarios). El profesor ve los pedidos en *Rutinas*, arma los ejercicios por día (series, repeticiones, descanso y notas) y el socio la ve en su portal. Para no empezar de cero, la *Biblioteca de rutinas* trae 15 rutinas semanales completas numeradas (R01 a R15) por objetivo, nivel y días; al armar la rutina se sugieren primero las que encajan con el pedido y el profesor las ajusta antes de guardar. Una rutina nueva reemplaza a la anterior y el profesor puede editarla. El rol `profesor` solo accede a *Rutinas* |
| Dashboard (admin) | Socios al día/vencidos, presentes ahora, ingresos del día/mes, asistencias de 7 días |
| **API REST** | En `/api` (JSON): aforo público y, con token de Laravel Sanctum obtenido con DNI + PIN, el perfil y las asistencias del socio. Ver la sección *API REST* |

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
2. Ingresar un PIN (se ve en la ficha de cada socio). Los socios de demostración tienen PIN fijo: Juan Pérez `111111`, Lucía Gómez `222222`, Carlos Romero `333333`, Ana Benítez `444444`, Martín Acosta `555555`; con su DNI y ese PIN también entran al portal del socio. Primer ingreso = **ingreso**; el mismo PIN otra vez = **egreso** con el tiempo de permanencia.
3. Casos de demostración: Ana Benítez (cuota vencida) y Martín Acosta (sin apto físico) deben ser **denegados**.

### Probar el portal del socio
1. Abrir `http://localhost:8000/socio`.
2. Ingresar el DNI y el PIN de un socio (el PIN se ve en su ficha).

### Probar las rutinas
El usuario profesor de prueba solo existe si la base se sembró con `SEED_DEMO=true`. En cualquier instalación (por ejemplo Render), el administrador lo crea en *Operadores* eligiendo el rol *Profesor*.

1. En el portal, entrar a *Mi rutina* y enviar un pedido.
2. Iniciar sesión como profesor → *Rutinas* → *Armar rutina* en el pedido. Elegir una rutina de la biblioteca y tocar *Cargar*, o escribirla de cero.
3. Volver al portal: la rutina aparece agrupada por día.

En una base ya instalada, correr `php artisan migrate` para crear las tablas de rutinas.

## API REST

Respuestas en JSON. Las rutas del socio usan un token **Bearer** de Laravel Sanctum que vence a los 30 días.

| Método | Ruta | Autenticación | Devuelve |
|---|---|---|---|
| GET | `/api/aforo` | Pública | Personas presentes, capacidad, porcentaje, nivel y franjas más concurridas |
| POST | `/api/socio/login` | DNI + PIN (bloqueo tras 5 intentos por DNI) | `token`, `tipo` y `vence` |
| GET | `/api/socio/perfil` | Token | Membresía, vencimiento de la cuota y del apto (sin PIN ni QR) |
| GET | `/api/socio/asistencias` | Token | Historial paginado; filtros `desde` y `hasta` |
| POST | `/api/socio/logout` | Token | Revoca el token usado |

```bash
curl http://localhost:8000/api/aforo
curl -X POST http://localhost:8000/api/socio/login -d dni=30111222 -d pin=111111
curl http://localhost:8000/api/socio/perfil -H "Authorization: Bearer <token>"
```

En una base ya instalada, correr `php artisan migrate` para crear la tabla `personal_access_tokens`.

## Pruebas automatizadas
```bash
php artisan test
```
Cubren: PIN/QR únicos, ingreso/egreso y permanencia, cuota vencida, apto vencido, egreso siempre permitido, tope de horas diarias, pase libre, límite de intentos, cálculo de vencimientos de pagos y control de roles.

## Despliegue en Render
Ver `DEPLOY.md`.

## Licencia

Software propietario. Titular: **Grupo 13** (Cardozo, Diego Fabián · Heretichi, Adriana · López, Gabriela Elvira).
El Gimnasio Fénix usa la **versión de prueba gratuita**; las versiones futuras serán pagas. Ver [`LICENSE`](LICENSE).

## Estructura
```
app/Services/AccesoService.php   reglas de ingreso/egreso y permanencia
app/Services/PagoService.php     alta de pagos y cálculo de vencimiento
app/Support/PlantillasRutina.php biblioteca de rutinas R01–R15 (editar acá para cambiarlas)
app/Livewire/                    PantallaAcceso, SociosIndex
app/Http/Controllers/            Socios, Pagos, Aptos, Asistencias, Membresías, Operadores, Rutinas, Dashboard
database/migrations/             modelo ER (users, membresias, socios, pagos, aptos_fisicos, asistencias)
```
