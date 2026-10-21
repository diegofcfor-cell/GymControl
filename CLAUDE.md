# Gym Control — guía para Claude

Sistema web de gestión y control de asistencia del **Gimnasio Fénix** (Formosa). Es el **Trabajo Final
Integrador (TFI)** de la Tecnicatura Universitaria en Programación, UTN FRRe sede Formosa (profesor: Eduardo
Roig; tutora y Product Owner: Viviana Machuca). Equipo: Cardozo (backend), Heretichi (full stack, pruebas y
despliegue), López (frontend).

**Objetivo único de todo cambio:** que el sistema cumpla y *demuestre* los requerimientos conceptuales del TIF.
Cada tarea tiene que poder explicarse en la defensa: qué requerimiento cubre, qué asignatura aplica y dónde está
en el código (ver "Mapa del TIF").

## Cómo trabajar con Diego

- Respondé en español. Diego es estudiante: **explicá el concepto antes del código o del comando** (qué es, para
  qué sirve, un ejemplo mínimo y el error más común).
- Al terminar, nombrá la asignatura y el requerimiento (RF/RNF/HU) que tocó el cambio, para que pueda defenderlo.
- Nada se mergea sin que Diego lo revise: trabajá en una rama y abrí PR.

## Stack

Laravel 12 · PHP 8.3 · Blade + Livewire 3 + Alpine.js · Tailwind CSS 4 (Vite 7) · MySQL 8 en producción,
SQLite en local y en tests · DomPDF · Simple QrCode · PHPUnit 11 · Docker · Render.

## Comandos

```bash
composer setup                 # primera vez sin Docker: deps, .env, clave, SQLite, migrate --seed, build
composer run dev               # servidor + cola + logs (pail) + Vite
php artisan serve              # solo el servidor → http://localhost:8000
docker compose up --build      # alternativa con Docker → http://localhost:8000
php artisan test               # todas las pruebas (SQLite en memoria, ver phpunit.xml)
php artisan test --filter=AccesoTest
vendor/bin/pint                # formato de código (estilo Laravel)
php artisan migrate            # tras agregar una migración
npm run build                  # compilar assets
```

Usuarios de prueba (`SEED_DEMO=true`): `admin@gymcontrol.test` / `Admin1234!`, `recepcion@gymcontrol.test` /
`Recepcion1234!`, `profesor@gymcontrol.test` / `Profesor1234!`. Socios demo con PIN fijo `111111` a `555555`.

## Estructura

```
app/Services/            reglas de negocio (Acceso, Pago, Aforo, Chatbot): acá va la lógica, no en el controlador
app/Http/Controllers/    panel de operadores; Portal/ = portal del socio
app/Http/Middleware/     RolMiddleware (`rol:administrador,recepcionista`), SocioAutenticado (`socio`)
app/Livewire/            PantallaAcceso, SociosIndex, AforoEnVivo, ChatbotFaq
app/Models/              Eloquent; Socio usa SoftDeletes (baja lógica)
app/Support/             PlantillasRutina (biblioteca R01–R15)
app/Console/Commands/    gym:actualizar-vencimientos (programado en routes/console.php)
database/migrations/     modelo ER; las nuevas llevan fecha 2026_MM_DD_
resources/views/         Blade por módulo; pdf/ para informes; components/layouts/ app, kiosco y portal
tests/Feature/           una prueba por módulo (Acceso, Pago, Roles, Portal, Aforo, Chatbot, Rutina, Web)
```

## Convenciones del código (respetarlas)

- **Nombres en español** en dominio, métodos, variables, rutas y tablas (`Socio::cuotaAlDia()`, `reglas()`,
  `aptos_fisicos`). Los textos de la interfaz también en español.
- Controladores delgados; las reglas de negocio van en `app/Services` con un docblock que las enumere.
- Validación con `$request->validate($this->reglas())` dentro del controlador (no hay Form Requests).
- Permisos por rol con el middleware `rol:` en `routes/web.php`; el portal del socio usa su propia sesión
  (`socio_id`) y el middleware `socio`. Nunca mezclar las dos sesiones.
- Intentos de login y de PIN limitados (`throttle`, `RateLimiter`). Archivos de aptos físicos en disco privado.
- Todo cambio de comportamiento lleva su prueba en `tests/Feature`. Antes de terminar: `php artisan test` y
  `vendor/bin/pint`.
- Si cambia una funcionalidad, actualizar la tabla de funcionalidades de `README.md` (y `DEPLOY.md` si cambia
  el despliegue).
- Nunca subir secretos: van en `.env` o en las variables de Render.

## Reglas de negocio que no se rompen

- Ingreso: socio activo **y** cuota vigente **y** apto físico vigente. El egreso **siempre** se permite y
  calcula la permanencia. Se avisa si se supera el tope diario del plan (Premium no tiene tope).
- Cada pago actualiza el vencimiento de la cuota (`PagoService`).
- PIN de 6 dígitos y QR (UUID) únicos por socio.
- Aforo: presentes = asistencias de hoy sin egreso; nivel libre < 50 %, moderado < 80 %, lleno ≥ 80 % de la
  capacidad configurada.
- Chatbot sin IA ni costo: busca por palabras clave; planes, precios y aforo se responden con datos en vivo.

## Mapa del TIF: asignatura → concepto → dónde se ve en Gym Control

Fuente: Tabla 9 del *Plan de Trabajo TFI v2* (13/10/2026), guía de coloquio de Base de Datos I y exámenes de
Programación IV. Al hacer un cambio, reforzá la evidencia de la fila que corresponda.

| Asignatura | Conceptos que hay que demostrar | Evidencia en el proyecto |
|---|---|---|
| Programación I–IV | Algoritmos, POO, MVC, ciclo de una petición (index.php → middleware → ruta → controlador → modelo → vista), Eloquent, autenticación, seguridad, APIs | `routes/web.php`, `app/Http`, `app/Models`, `app/Services`, componentes Livewire |
| Base de Datos I y II | MER → modelo relacional, PK/FK, integridad referencial, normalización 1FN–3FN, índices, DDL/DML, funciones agregadas, GROUP BY/HAVING, respaldos | `database/migrations`, consultas del dashboard y de `AforoService`, `mysqldump` en `DEPLOY.md` |
| Metodología de Sistemas I y II | Análisis, requerimientos RF/RNF, casos de uso, UML, ciclo de vida, Gantt y PERT | RF01–RF09 y RNF01–RNF06 del Plan; cada función nueva debe sumarse a casos de uso y diagramas |
| Gestión de Desarrollo de Software | Scrum, Kanban, backlog, historias de usuario (HU1–HU12), criterios de aceptación, Git/GitHub, definición de hecho | Ramas por funcionalidad y PR; HU implementada + probada + integrada en `main` |
| Arquitectura y Sistemas Operativos | Cliente-servidor, Linux, procesos y servicios, contenedores, despliegue | `Dockerfile`, `docker/`, `docker-compose.yml`, `render.yaml`, tarea programada |
| Matemática | Lógica, conjuntos, relaciones, cálculo | Condiciones de acceso (Y lógico), cálculo de vencimientos y permanencia, porcentaje de aforo |
| Probabilidad y Estadística | Estadística descriptiva, promedios, interpretación de indicadores, PERT (te = (a + 4m + b) / 6) | Dashboard, promedio de horas pico de 4 semanas en el aforo |
| Introducción al Análisis de Datos | Recolección, procesamiento e interpretación de datos | Indicadores de asistencias, pagos y ocupación; informes PDF |
| Organización Empresarial | Procesos de la organización, relevamiento | Procesos del gimnasio que pasan de papel a sistema (alta, cobro, acceso) |
| Legislación | Ley 25.326 de Protección de Datos Personales, datos de salud, responsabilidad profesional | Aptos físicos en disco privado, control por roles, baja lógica, contraseñas con bcrypt |
| Inglés I y II | Lectura de documentación técnica | Uso de la documentación oficial de Laravel, Livewire, Tailwind, Docker y Render |

## Brechas conocidas entre el Plan del TIF y el código

El Plan describe dos roles (administrador y recepcionista) y un socio que solo marca su PIN. El código ya suma
el rol **profesor**, el **portal del socio**, el **aforo en vivo**, el **chatbot** y las **rutinas**. Cuando
toques esas partes, recordale a Diego que estas funciones nuevas tienen que entrar en la documentación del TIF:
nuevos RF e HU, casos de uso del socio y del profesor, matriz de permisos, modelo ER (`configuraciones`,
`preguntas_frecuentes`, rutinas) y las tablas de asignaturas.

## Pendiente

Esta guía se armó con los documentos de Google Drive porque NotebookLM no tiene conexión con Claude. Si los
cuadernos de la colección `UTN_Tecnico_Programacion` agregan conceptos que no figuran en la tabla, sumarlos
como filas o columnas del "Mapa del TIF".
