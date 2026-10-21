# Gym Control — guía para Claude

Sistema web de gestión y control de asistencia del **Gimnasio Fénix** (Formosa). Es el **Trabajo Final
Integrador (TIF)** de la Tecnicatura Universitaria en Programación, UTN FRRe sub sede Formosa (cátedra TIF,
comisión 2.1: Mg. Federico Britez; profesor en los documentos del grupo: Eduardo Roig; tutora y Product Owner:
Viviana Machuca). Equipo: Cardozo (backend), Heretichi (full stack, pruebas y despliegue), López (frontend).

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
- Controladores delgados (SRP): Controlador → Servicio (`app/Services`, con un docblock que enumere las reglas)
  → Modelo Eloquent. Inyectar los servicios por parámetro (DIP), no crearlos con `new` dentro de la clase.
- Validación: el código existente usa `$request->validate($this->reglas())`. Para **controladores nuevos** usar
  Form Request (`php artisan make:request`), que es lo que pide Programación IV. No reescribir lo existente sin
  pedido.
- Permisos por rol con el middleware `rol:` en `routes/web.php`; para permisos sobre un registro puntual, usar
  Policies. El portal del socio usa su propia sesión (`socio_id`) y el middleware `socio`. Nunca mezclar las
  dos sesiones.
- Intentos de login y de PIN limitados (`throttle`, `RateLimiter`). Archivos de aptos físicos en disco privado.
- Patrones de diseño solo con justificación: antes de aplicar uno, responder 1) ¿el problema ya apareció 2 o 3
  veces?, 2) ¿el cambio es probable o imaginario?, 3) ¿qué consecuencia negativa introduce? Si no hay
  respuesta, no se aplica. Al refactorizar, borrar el código viejo (no dejarlo "por las dudas").
- Dependencias solo con Composer o npm; nunca copiar código de terceros a mano.
- Todo cambio de comportamiento lleva su prueba en `tests/Feature`. Antes de terminar: `php artisan test` y
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
- Cada pago actualiza el vencimiento de la cuota (`PagoService`).
- PIN de 6 dígitos y QR (UUID) únicos por socio.
- Aforo: presentes = asistencias de hoy sin egreso; nivel libre < 50 %, moderado < 80 %, lleno ≥ 80 % de la
  capacidad configurada.
- Chatbot sin IA ni costo: busca por palabras clave; planes, precios y aforo se responden con datos en vivo.

## Mapa del TIF: asignatura → concepto → dónde se ve en Gym Control

Fuentes: Tabla 9 del *Plan de Trabajo TFI v2* (13/10/2026), guía de coloquio de Base de Datos I, exámenes de
Programación IV y los seis resúmenes de cátedra que compartió Diego (Guía del TIF, Programación IV,
Metodología de Sistemas II, Gestión de Desarrollo de Software, Introducción al Análisis de Datos y
Legislación). Al hacer un cambio, reforzá la evidencia de la fila que corresponda.

| Asignatura | Conceptos que hay que demostrar | Evidencia en el proyecto |
|---|---|---|
| Programación I–IV | Algoritmos, POO, MVC, ciclo de una petición (index.php → middleware → ruta → controlador → modelo → vista), Controlador → Servicio → Repositorio, Eloquent, autenticación, Policies, Form Request, APIs REST con Sanctum, tiempo real con Reverb + Echo | `routes/web.php`, `app/Http`, `app/Models`, `app/Services`, componentes Livewire |
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

- **Arquitectura:** MVC extendido a Controlador → Servicio → Repositorio. Gym Control ya tiene la capa de
  servicios; un repositorio se justifica solo si una consulta se repite en varios servicios.
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
  Hay que definir quién es titular de Gym Control (equipo o gimnasio) y bajo qué licencia se entrega.
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
2. **Sin API REST ni Sanctum** (Programación IV y Guía del TIF, que pide API REST en la arquitectura): no hay
   `routes/api.php`. Un candidato natural es exponer el aforo o el historial del socio como API.
3. **Sin tiempo real con WebSockets** (Programación IV): el aforo se actualiza con `wire:poll.30s`. Reverb +
   Echo permitirían emitir un evento en cada ingreso o egreso.
4. **Sin Form Requests ni Policies** (Programación IV): la validación está en los controladores.
5. **CI incompleto** (Metodología II): `.github/workflows/tests.yml` corre en push a `master`, pero la rama
   principal es `main`, y no ejecuta Pint como linter.
6. **Falta `docs/DEUDA-TECNICA.md`** y la documentación de los 4 patrones con su rama y PR (Metodología II).
7. **Convenciones Git:** las ramas existentes usan `feature/…` y los commits no siguen `tipo(ámbito):`. Desde
   ahora, usar la convención de la cátedra.
8. **Licencia y titularidad** (Legislación): `composer.json` declara licencia MIT (software libre), pero el
   sistema es software a medida para el Gimnasio Fénix. Hay que decidir titular y licencia y documentarlo.
9. **Datos personales** (Legislación): no hay texto de privacidad ni consentimiento para los datos de salud,
   ni un procedimiento para que el socio pida rectificar o suprimir sus datos.

## Fuentes de esta guía

NotebookLM no tiene conexión con Claude: esta guía se armó con los documentos de Google Drive y los seis
resúmenes de cátedra de la colección `UTN_Tecnico_Programacion` que compartió Diego. Si llega material de otra
asignatura, sumarlo a "Exigencias por asignatura", al "Mapa del TIF" y a "Brechas".
