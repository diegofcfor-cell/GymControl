# Gym Control — guía para Claude

Sistema web de gestión y control de asistencia del **Gimnasio Fénix** (Formosa). Es el **Trabajo Final
Integrador (TIF)** de la Tecnicatura Universitaria en Programación, UTN FRRe sub sede Formosa (profesor de la
comisión: Eduardo Roig; tutora y Product Owner: Viviana Machuca). Equipo: Cardozo (backend), Heretichi (full
stack, pruebas y despliegue), López (frontend).

**Objetivo único de todo cambio:** que el sistema cumpla y *demuestre* los requerimientos conceptuales del TIF.
Cada tarea tiene que poder explicarse en la defensa: qué requerimiento cubre, qué asignatura aplica y dónde está
en el código (ver "Mapa del TIF" y "Exigencias por asignatura").

## Cómo trabajar con Diego

- Respondé en español. Diego es estudiante: **explicá el concepto antes del código o del comando** (qué es, para
  qué sirve, un ejemplo mínimo y el error más común).
- Actuá como arquitecto de software y asesor académico del TIF: proponé soluciones con SOLID y modularidad,
  y señalá cuándo algo no alcanza para lo que pide la cátedra.
- Al terminar, nombrá la asignatura y el requerimiento (RF/RNF/HU) que tocó el cambio, para que pueda defenderlo.
- Las tareas nuevas se escriben como historia de usuario ("Como … quiero … para …") con criterios de
  aceptación y estado To Do / In Progress / Done.
- Nada se mergea sin que Diego lo revise: trabajá en una rama y abrí PR.

## El TIF: qué exige la cátedra

- **Requisitos técnicos:** resolver un problema real de gestión; aplicación web con base de datos estructurada
  y **desplegada en un hosting**; arquitectura cliente/servidor con frontend, backend, **API REST** y gestor de
  base de datos; Git y GitHub más un tablero (Jira); Scrum con Product Backlog, sprints, historias de usuario
  con criterios de aceptación y definición de hecho.
- **Fases:** 1) preselección (3 propuestas); 2) Plan de Trabajo, maquetación de la BD, informe de avance y
  **video explicativo, preferentemente en inglés** (requisito para regularizar); 3) informe final y defensa
  oral ante tribunal (código en GitHub, pruebas y conclusiones). Mínimo 95 horas por estudiante.
- **Informe final:** 20 a 30 páginas A4 sin anexos, márgenes superior e izquierdo 2,5 cm, inferior y derecho
  2,0 cm, citas APA 7.ª edición o IEEE. Secciones: portada; 1) resumen (máx. 300 palabras) y 4 a 6 palabras
  clave; 2) introducción; 3) problema y justificación; 4) objetivos; 5) alcance y limitaciones (dentro y
  fuera); 6) marco teórico y tecnológico; 7) metodología y planificación (Scrum, roles, Gantt, arquitectura);
  8) resultados y pruebas; 9) conclusiones y trabajos futuros; 10) bibliografía; 11) anexos (manual de usuario,
  diagramas ER/UML, enlace al repositorio).
- **Plan de Trabajo:** ficha de alumnos y roles, datos del cliente y tutor, matriz de etapas (mínimo 5 tareas
  por etapa, con horas y resultados), backlog con HU codificadas, prioridad, estado y criterios de aceptación.
- **Redacción de informes:** tono técnico, formal y académico, en español neutro, con fuentes citadas.

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
vendor/bin/pint                # formato de código (estilo Laravel); --test solo verifica
php artisan migrate            # tras agregar una migración
npm run build                  # compilar assets
```

Usuarios de prueba (`SEED_DEMO=true`): `admin@gymcontrol.test` / `Admin1234!`, `recepcion@gymcontrol.test` /
`Recepcion1234!`, `profesor@gymcontrol.test` / `Profesor1234!`. Socios demo con PIN fijo `111111` a `555555`.

## Estructura

```
app/Services/            reglas de negocio (Acceso, Pago, Aforo, Chatbot, AutenticacionSocio, SolicitudDatos): acá va la lógica
app/Http/Controllers/    panel de operadores; Portal/ = portal del socio; Api/ = API REST
app/Http/Middleware/     RolMiddleware (`rol:`), SocioAutenticado (`socio`), SocioConToken (`socio.token`)
app/Http/Requests/       Form Requests de cada formulario; Resources/ = respuestas JSON de la API
app/Policies/            permisos por registro: SocioPolicy, AptoFisicoPolicy, RutinaPolicy
routes/api.php           API REST: /api/aforo pública; /api/socio/* con token de Sanctum (DNI + PIN)
app/Livewire/            PantallaAcceso, SociosIndex, AforoEnVivo, ChatbotFaq
app/Models/              Eloquent; Socio usa SoftDeletes (baja lógica)
app/Repositories/        AsistenciaRepository: consultas que comparten varios módulos (presentes hoy)
app/Support/             PlantillasRutina (biblioteca R01–R15)
app/Console/Commands/    gym:actualizar-vencimientos (programado en routes/console.php)
database/migrations/     modelo ER; las nuevas llevan fecha 2026_MM_DD_
resources/views/         Blade por módulo; pdf/ para informes; components/layouts/ app, kiosco y portal
tests/Feature/           una prueba por módulo (Acceso, Pago, Roles, Portal, Aforo, Chatbot, Rutina, Web, Gestion, Privacidad)
tests/Unit/              reglas puras sin base de datos (período de pago, nivel de aforo, permanencia, chatbot)
```

## Convenciones del código (respetarlas)

- **Nombres en español** en dominio, métodos, variables, rutas y tablas (`Socio::cuotaAlDia()`, `reglas()`,
  `aptos_fisicos`). Los textos de la interfaz también en español.
- Controladores delgados (SRP): Controlador → Servicio (`app/Services`, con un docblock que enumere las reglas)
  → Modelo Eloquent. Inyectar los servicios por parámetro (DIP), no crearlos con `new` dentro de la clase.
- Validación: con Form Request (`php artisan make:request`), que es lo que pide Programación IV. Cada clase lleva
  un docblock con sus reglas de negocio, igual que los servicios.
- Permisos por rol con el middleware `rol:` en `routes/web.php`; para permisos sobre un registro puntual, usar
  Policies. El portal del socio usa su propia sesión (`socio_id`) y el middleware `socio`. Nunca mezclar las
  dos sesiones.
- Intentos de login y de PIN limitados (`throttle`, `RateLimiter`). Archivos de aptos físicos en disco privado.
- Patrones de diseño solo con justificación: antes de aplicar uno, responder 1) ¿el problema ya apareció 2 o 3
  veces?, 2) ¿el cambio es probable o imaginario?, 3) ¿qué consecuencia negativa introduce? Si no hay
  respuesta, no se aplica. Al refactorizar, borrar el código viejo (no dejarlo "por las dudas").
- Dependencias solo con Composer o npm; nunca copiar código de terceros a mano.
- Todo cambio de comportamiento lleva su prueba en `tests/Feature`; un cálculo puro (sin base de datos) va además
  en `tests/Unit`. Antes de terminar: `php artisan test` y
  `vendor/bin/pint`.
- Si cambia una funcionalidad, actualizar la tabla de funcionalidades de `README.md` (y `DEPLOY.md` si cambia
  el despliegue).
- Nunca subir secretos: van en `.env` o en las variables de Render. En producción, `APP_DEBUG=false`.

## Flujo Git y Pull Requests (Metodología de Sistemas II)

- Nunca push directo a `main`: rama de trabajo + PR. Nombres de rama: `feat/…`, `fix/…`, `refactor/…`,
  `docs/…` (un patrón de diseño nuevo va en su propia rama, p. ej. `feat/patron-strategy`).
- Commits con el formato `tipo(ámbito): descripción` (`feat(aforo): …`, `fix(pagos): …`, `docs: …`), pequeños
  y atómicos.
- La descripción del PR enlaza su issue con `Closes #N`. Si piden cambios en la revisión, se commitean en la
  misma rama: no se abre un PR nuevo.
- Conflictos: rebase sobre `main`, resolver, probar y subir con `--force-with-lease` (nunca `--force`).
- Versionado semántico `MAJOR.MINOR.PATCH`; el deploy a producción se dispara con un tag `vX.Y.Z`.
- El revisor evalúa corrección (¿hace lo que promete?), alcance (¿toca solo lo necesario?) y legibilidad.

## Reglas de negocio que no se rompen

- Ingreso: socio activo **y** cuota vigente **y** apto físico vigente. El egreso **siempre** se permite y
  calcula la permanencia. Se avisa si se supera el tope diario del plan (Premium no tiene tope).
- Pagos (`PagoService`, en una transacción): si la cuota sigue vigente, el nuevo período empieza el día
  siguiente al vencimiento; si ya venció, empieza el día del pago. `periodo_hasta` = inicio + `duracion_dias` − 1
  y pasa a `socios.fecha_vencimiento`; el socio queda activo con la membresía pagada.
- Apto vigente = tiene un apto con `fecha_vencimiento >= hoy`. El comando `gym:actualizar-vencimientos` corre
  todos los días a las 00:05 y marca como `vencido` el `estado` de los aptos vencidos.
- PIN de 6 dígitos y QR (UUID) únicos por socio.
- Aforo: presentes = asistencias de hoy sin egreso; nivel libre < 50 %, moderado < 80 %, lleno ≥ 80 % de la
  capacidad configurada.
- Chatbot sin IA ni costo: busca por palabras clave; planes, precios y aforo se responden con datos en vivo.

## Modelo de datos (3FN)

Seis tablas núcleo en tercera forma normal, con PK `id`, FK con `constrained()` e índices en DNI, PIN, QR y
fechas:

| Tabla | Campos clave | Relaciones y notas |
|---|---|---|
| `users` | `email` (UK), `password` (bcrypt), `rol`, `activo` | Operadores: administrador, recepcionista, profesor |
| `membresias` | `nombre`, `precio`, `duracion_dias`, `horas_diarias_max` (NULL = pase libre), `estado` | Planes Básica, Media y Premium |
| `socios` | `dni` (UK), `pin` (UK, 6 dígitos), `qr_codigo` (UK, UUID), `estado`, `fecha_alta`, `fecha_vencimiento`, `deleted_at` | FK a `membresias` y a `users` (quién lo dio de alta); baja lógica |
| `pagos` | `importe`, `fecha_pago`, `periodo_desde`, `periodo_hasta`, `metodo_pago` | FK a `socios`, `membresias` y `users` (quién cobró) |
| `aptos_fisicos` | `fecha_emision`, `fecha_vencimiento`, `estado`, `archivo` (disco privado) | FK a `socios` y `users` |
| `asistencias` | `fecha` (índice), `hora_ingreso`, `hora_salida` (NULL = en sala), `tiempo_permanencia` | FK a `socios`; índice (`socio_id`, `fecha`) |

**Redundancias controladas (justificarlas en la defensa):** `pagos.importe` guarda el precio cobrado para que un
cambio de tarifa no altere el historial; `socios.fecha_vencimiento` guarda el fin de la cuota para validar el
acceso con una sola consulta. Tablas agregadas después del Plan: `configuraciones`, `preguntas_frecuentes`,
rutinas (`solicitudes_rutina`, `rutinas`, `rutina_ejercicios`), `solicitudes_datos` y `personal_access_tokens`
(Sanctum).

## Matriz de permisos

| Funcionalidad | Admin | Recepción | Profesor | Socio (portal/API) |
|---|---|---|---|---|
| Socios (alta, edición, PIN/QR), aptos, pagos, pantalla de acceso, historiales y PDF | Sí | Sí | — | — |
| Baja lógica de socios, membresías, operadores, preguntas, configuración, dashboard económico | Sí | — | — | — |
| Responder pedidos de rectificación y supresión de datos | Sí | — | — | — |
| Rutinas: ver pedidos, armar y editar, biblioteca R01–R15 | Sí | — | Sí | — |
| Ver su membresía, asistencias, pagos, aptos, aforo, chatbot, pedir rutina y pedir rectificar o suprimir sus datos | — | — | — | Sí |

El Plan del TIF solo tiene las columnas de administrador y recepcionista (ver "Brechas", punto 1).

## Planificación del Plan de Trabajo

450 horas de equipo (150 por integrante) en 13 semanas (65 días hábiles): 5 semanas de análisis y diseño y
4 sprints de 2 semanas. Scrum Master: Heretichi. Product Owner: la tutora.

- **Sprint 1 — base y seguridad:** HU1 login/logout, HU10 gestión de operadores, HU12 rutas protegidas por
  middleware.
- **Sprint 2 — gestión central:** HU2 socios con buscador Livewire, HU3 membresías, HU8 aptos físicos.
- **Sprint 3 — finanzas e identificación:** HU4 pagos y vencimientos, HU5 PIN y QR.
- **Sprint 4 — acceso, dashboard y despliegue:** HU6 acceso por PIN, HU7 acceso por QR, HU11 historial en PDF,
  HU9 dashboard; pruebas PHPUnit, Dockerfile y Render.

Las funciones posteriores (portal, aforo, chatbot, rutinas, API) todavía no tienen HU asignadas.

## Mapa del TIF: asignatura → concepto → dónde se ve en Gym Control

Fuentes: Tabla 9 del *Plan de Trabajo TFI v2* (13/10/2026), guía de coloquio de Base de Datos I, exámenes de
Programación IV y los seis resúmenes de cátedra que compartió Diego (Guía del TIF, Programación IV,
Metodología de Sistemas II, Gestión de Desarrollo de Software, Introducción al Análisis de Datos y
Legislación). Al hacer un cambio, reforzá la evidencia de la fila que corresponda.

| Asignatura | Conceptos que hay que demostrar | Evidencia en el proyecto |
|---|---|---|
| Programación I–IV | Algoritmos, POO, MVC, ciclo de una petición (index.php → middleware → ruta → controlador → modelo → vista), Controlador → Servicio → Repositorio, Eloquent, autenticación, Policies, Form Request, APIs REST con Sanctum, tiempo real con Reverb + Echo | `routes/web.php`, `routes/api.php`, `app/Http`, `app/Models`, `app/Services`, componentes Livewire |
| Base de Datos I y II | MER → modelo relacional, PK/FK, integridad referencial, normalización 1FN–3FN, índices, DDL/DML, funciones agregadas, GROUP BY/HAVING, respaldos | `database/migrations`, consultas del dashboard y de `AforoService`, `mysqldump` en `DEPLOY.md` |
| Metodología de Sistemas I y II | Análisis, casos de uso, UML, ciclo de vida; Git y PR, SOLID, patrones de diseño, refactorización, deuda técnica, CI/CD, verificación y validación, pirámide de pruebas | Ramas y PR en GitHub, `app/Services`, `.github/workflows/tests.yml`, `tests/Feature` |
| Gestión de Desarrollo de Software | Ciclo de vida y modelos de proceso, Scrum/Kanban, ingeniería de requerimientos, ERS IEEE, casos de uso, alcance y EDT, Puntos de Función, COCOMO, PERT, ISO/IEC 25010, KPIs, manuales | RF01–RF09, RNF01–RNF06 y HU1–HU12 del Plan; Gantt y PERT; README (manual técnico) |
| Arquitectura y Sistemas Operativos | Cliente-servidor, Linux, procesos y servicios, contenedores, despliegue | `Dockerfile`, `docker/`, `docker-compose.yml`, `render.yaml`, tarea programada |
| Matemática | Lógica, conjuntos, relaciones, cálculo | Condiciones de acceso (Y lógico), cálculo de vencimientos y permanencia, porcentaje de aforo |
| Probabilidad y Estadística | Estadística descriptiva, promedios, interpretación de indicadores, PERT (te = (a + 4m + b) / 6) | Dashboard, promedio de horas pico de 4 semanas en el aforo |
| Introducción al Análisis de Datos | Ciclo de vida del análisis (problema, obtención, limpieza, exploración, modelado, comunicación), tipos de variables, imputación de faltantes, elección del gráfico según el objetivo, KPIs | Aforo y gráfico de horas pico, dashboard, informes PDF |
| Organización Empresarial | Procesos de la organización, relevamiento | Procesos del gimnasio que pasan de papel a sistema (alta, cobro, acceso) |
| Legislación | Ley 25.326 (datos personales, datos de salud, hábeas data), Ley 11.723 (propiedad intelectual del software), Ley 25.506 (firma digital), Ley 26.388 (delitos informáticos), responsabilidad por decisiones automatizadas (art. 1757 CCyC) | Aptos físicos en disco privado, control por roles, baja lógica, contraseñas con bcrypt, límite de intentos |
| Inglés I y II | Lectura de documentación técnica | Uso de la documentación oficial de Laravel, Livewire, Tailwind, Docker y Render |

## Exigencias por asignatura

### Programación IV (backend profesional con Laravel)

- **Arquitectura:** MVC extendido a Controlador → Servicio → Repositorio. Un repositorio se justifica solo si
  una consulta se repite en varios lugares: por eso existe `AsistenciaRepository` (presentes hoy, HU23) y no
  uno por tabla.
- **Seguridad:** Form Request para validar, Policies (`viewAny`, `view`, `update`, `delete`) para permisos
  por registro, contraseñas con hash, `APP_DEBUG=false` y `composer install --no-dev --optimize-autoloader` en
  producción.
- **APIs REST:** rutas en `routes/api.php`, autenticación con Sanctum (`php artisan install:api`, trait
  `HasApiTokens`, `createToken('api')->plainTextToken`, `Authorization: Bearer`, middleware `auth:sanctum`,
  revocar con `currentAccessToken()->delete()`). JWT (`tymon/jwt-auth`) es la alternativa sin estado; nunca
  guardar tokens en `localStorage`.
- **Tiempo real:** eventos `ShouldBroadcast` + Laravel Reverb (`php artisan install:broadcasting --reverb`,
  `php artisan reverb:start`, puerto 8080) + Laravel Echo en el navegador. Canales públicos, privados
  (autorizados en `routes/channels.php`) y de presencia. Variables `BROADCAST_CONNECTION=reverb`,
  `REVERB_*` y `VITE_REVERB_*`.

### Metodología de Sistemas II (calidad y proceso de desarrollo)

- **SOLID:** SRP (una sola razón para cambiar), OCP (extender sin modificar código probado), LSP, ISP, DIP
  (depender de abstracciones e inyectar dependencias).
- **Patrones de la cátedra:** Factory, Strategy, Observer, Adapter, Decorator, Facade, MVC + Repository.
  Candidatos ya presentes para explicar en la defensa (verificarlos antes de afirmarlo): servicios como
  Facade de cada módulo, eventos de modelo de Eloquent (`Socio::booted`) como Observer, middleware como
  cadena de filtros.
- **CI/CD:** el pipeline hace instalación limpia → linter → pruebas en cada push y PR, y bloquea el merge si
  no está en verde. El deploy se dispara con un tag de versión.
- **Verificación y validación:** verificación = ¿lo construimos bien? (cumple la especificación); validación =
  ¿construimos lo correcto? (resuelve la necesidad del gimnasio). Pirámide: unitarias → integración → E2E.
- **Entregables del repositorio:** README reproducible, `docs/DEUDA-TECNICA.md` (síntoma → deuda → patrón o
  solución → consecuencia negativa asumida), mínimo 4 patrones cada uno con su rama y PR cerrado, commits con
  convención y pipeline de CI con linter y tests en verde.

### Gestión de Desarrollo de Software (gestión del proyecto)

- **Requerimientos:** niveles de negocio, usuario y software; RF (qué hace) y RNF (cómo se comporta: del
  producto, organizacionales y externos como la Ley 25.326). Cada RF nuevo se escribe como ficha: entradas,
  proceso y salidas, con prioridad Esencial, Útil o Deseable.
- **ERS según IEEE:** correcta, sin ambigüedades, completa, consistente, verificable, priorizada, modificable y
  trazable. Trazable significa poder ir del RF a la HU, al código y a la prueba que lo verifica.
- **Casos de uso:** funcionalidades completas que dan valor al actor, no pasos de la interfaz; usar
  `include`/`extend` con moderación.
- **Estimación:** Puntos de Función (entradas, salidas, consultas, archivos lógicos internos = tablas,
  interfaces externas); COCOMO básico `PM = a·KLOC^b`, `TDEV = c·PM^d` (orgánico: a 2.4, b 1.05, c 2.5,
  d 0.38); PERT `te = (O + 4M + P) / 6`, varianza `((P − O) / 6)²`, ruta crítica con holgura cero.
- **Calidad ISO/IEC 25010:** confiabilidad, eficiencia, usabilidad, mantenibilidad y portabilidad. Cada RNF del
  Plan debería poder ubicarse en uno de estos atributos.
- **Artefactos:** ERS, documento de diseño, repositorio con pruebas, manual de usuario y manual técnico, KPIs
  (velocidad del equipo, densidad de defectos, cumplimiento de hitos).

### Introducción al Análisis de Datos (de los datos a la decisión)

- **Ciclo de vida:** 1) definir la pregunta de negocio, 2) obtener los datos, 3) limpiarlos (nulos,
  duplicados, fuera de rango), 4) explorarlos, 5) modelarlos, 6) comunicar el resultado. Toda métrica nueva
  del sistema debe empezar por la pregunta que responde (p. ej. "¿a qué hora conviene ir al gimnasio?").
- **Fuentes:** internas (la base de Gym Control), externas (APIs, datos abiertos) y creadas (encuestas).
- **Tipos de variables:** nominales (plan de membresía), ordinales (nivel de aforo libre/moderado/lleno),
  discretas (personas presentes) y continuas (permanencia, importe). El tipo define qué cálculo y qué gráfico
  corresponden.
- **Limpieza e imputación:** media, mediana (robusta ante atípicos), moda (categóricas) o valor constante
  cuando el faltante tiene significado. Ejemplo en el código: una asistencia sin egreso se cuenta con
  `AforoService::PERMANENCIA_ESTIMADA` = 60 minutos (imputación por valor constante).
- **Gráfico según el objetivo:** distribución → histograma o boxplot; comparar categorías → barras;
  evolución en el tiempo → líneas; relación entre variables → dispersión o mapa de calor; partes de un todo →
  treemap o anillo. Para análisis fuera del sistema: Excel para datos chicos, Power BI o Looker Studio para
  tableros, Python con Pandas para volumen o automatización.

### Legislación (ejercicio profesional responsable)

- **Ley 25.326 de Protección de Datos Personales:** los aptos físicos son datos de salud (sensibles). El socio
  tiene derecho de acceso, rectificación y supresión (hábeas data, art. 43 C.N.). El portal del socio cubre el
  acceso; la baja lógica conserva el historial, así que hay que justificar por qué se conservan esos datos.
- **Ley 11.723 de Propiedad Intelectual:** protege el software y las bases de datos (la expresión, no la idea).
  Gym Control pertenece al Grupo 13 y se entrega con licencia de uso de prueba gratuita (`LICENSE`).
- **Ley 25.506 de Firma Digital:** la firma digital presume autoría e integridad; los comprobantes PDF del
  sistema no están firmados digitalmente, por lo que no tienen ese valor.
- **Ley 26.388 de Delitos Informáticos:** acceso indebido, phishing y daño a datos. Justifica el límite de
  intentos de PIN y login, el hash de contraseñas y los roles.
- **Decisiones automatizadas:** quien implementa el sistema responde por sus daños (art. 1757 CCyC). La
  pantalla de acceso deniega el ingreso de forma automática: el motivo debe mostrarse siempre y recepción
  debe poder resolverlo.
- **Cómo analizar un caso legal:** 1) problema jurídico, 2) norma y principios aplicables, 3) derechos y
  obligaciones, 4) conclusión fundamentada, citando siempre la norma argentina.

## Brechas entre lo que piden las asignaturas y el código

Cuando una tarea toque alguna de estas partes, recordale a Diego la brecha. No las resuelvas sin que lo pida.

1. **Plan del TIF desactualizado:** el Plan describe dos roles y un socio que solo marca su PIN. El código ya
   suma el rol **profesor**, el **portal del socio**, el **aforo**, el **chatbot** y las **rutinas**. Faltan
   sus RF e HU, casos de uso del socio y del profesor, la matriz de permisos y el modelo ER (`configuraciones`,
   `preguntas_frecuentes`, rutinas).
2. ~~Sin API REST ni Sanctum~~ **resuelta:** `routes/api.php` expone el aforo público y el perfil y las
   asistencias del socio con tokens de Sanctum (ver README, sección *API REST*).
3. **Sin tiempo real con WebSockets** (Programación IV): el aforo se actualiza con `wire:poll.30s`. Reverb +
   Echo permitirían emitir un evento en cada ingreso o egreso.
4. ~~Pocas Form Requests y sin Policies~~ **resuelta:** todo formulario que guarda datos valida con una Form
   Request (`app/Http/Requests`) y hay Policies para socios, aptos y rutinas (`app/Policies`). Solo los filtros
   de búsqueda por GET (historial del portal y asistencias de la API) validan en el controlador.
5. ~~CI incompleto~~ **resuelta:** `.github/workflows/tests.yml` corre en `main` y en cada PR con
   instalación → Pint (`pint --test`) → pruebas. Falta que la regla de la rama `main` exija el check en verde
   (se configura en GitHub, Settings → Rules).
6. **`docs/DEUDA-TECNICA.md` iniciado** (Metodología II): documenta Repository (HU23). Faltan Observer,
   Strategy y Facade, cada uno con su rama y PR.
7. **Convenciones Git:** las ramas existentes usan `feature/…` y los commits no siguen `tipo(ámbito):`. Desde
   ahora, usar la convención de la cátedra.
8. ~~Licencia y titularidad~~ **resuelta:** titular el Grupo 13 (Cardozo, Heretichi, López); el Gimnasio
   Fénix usa la versión de prueba gratuita y las próximas versiones serán pagas. Ver `LICENSE` (Ley 11.723).
9. ~~Datos personales~~ **resuelta** (Legislación): aviso de privacidad (`/privacidad`), consentimiento con
   fecha (`socios.consentimiento_datos_at`, exigido para cargar el apto) y pedidos de rectificación o supresión
   desde *Mis datos* del portal, que responde el administrador en 5 días hábiles (`solicitudes_datos`, HU27).
   Responder no borra datos: la supresión real sigue siendo la baja lógica, y hay que justificar en la defensa
   por qué se conserva el historial de pagos.

## Fuentes de esta guía

NotebookLM no tiene conexión con Claude: esta guía se armó con los documentos de Google Drive, los seis
resúmenes de cátedra de la colección `UTN_Tecnico_Programacion` y el resumen del proyecto Gym Control (modelo de
datos, reglas, permisos y sprints) que compartió Diego, verificados contra el código. Si llega material de otra
asignatura, sumarlo a "Exigencias por asignatura", al "Mapa del TIF" y a "Brechas".
