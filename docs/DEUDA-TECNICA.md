# Deuda técnica y patrones de diseño — Gym Control

Registro que pide Metodología de Sistemas II. Cada entrada sigue el formato **síntoma → deuda → patrón o
solución → consecuencia negativa asumida**, y responde las tres preguntas que el equipo se hace antes de aplicar
un patrón: 1) ¿el problema ya apareció 2 o 3 veces?, 2) ¿el cambio es probable o imaginario?, 3) ¿qué
consecuencia negativa introduce?

| # | Patrón | Historia | Estado |
|---|---|---|---|
| 1 | Repository | HU23 | Aplicado |
| 2 | Observer | HU21 | Aplicado |
| 3 | Strategy | HU22 | Pendiente (Sprint 4) |
| 4 | Facade | HU24 | Pendiente (Sprint 4) |

## 1. Repository — consulta de "presentes hoy" (HU23)

**Síntoma.** La regla "un socio está presente si tiene una asistencia de hoy sin egreso" estaba escrita cinco
veces en cuatro clases, siempre como `abiertas()->whereDate('fecha', today())`:

| Dónde | Para qué |
|---|---|
| `AforoService::presentes()` | Contar personas para el aforo |
| `AccesoService::procesar()` | Decidir si el código registra un ingreso o un egreso |
| `DashboardController::index()` (dos veces) | Mostrar el total y la lista de presentes |
| `Portal\PortalController::inicio()` | Avisarle al socio que está dentro |

**Deuda.** Conocimiento duplicado (viola DRY y SRP): si la regla cambia, hay que encontrar y modificar las
cinco copias, y alcanza con olvidar una para que el aforo, el dashboard y la pantalla de acceso no coincidan.

**Las tres preguntas.**
1. ¿Se repite? Sí: cinco veces, en dos servicios y dos controladores.
2. ¿El cambio es probable? Sí. Hoy una asistencia de ayer sin egreso (el socio olvidó marcar la salida) deja
   de contar como presente a medianoche; es esperable que el gimnasio pida cerrarlas solas o usar una ventana
   de horas en lugar del día calendario.
3. ¿Qué consecuencia negativa introduce? Una clase y una indirección más (ver abajo).

**Solución.** `app/Repositories/AsistenciaRepository.php` concentra la regla en un método privado,
`presentes()`, y expone cuatro consultas con nombre del dominio: `cantidadPresentes()`, `presentesHoy()`,
`abiertaDeHoy(Socio)` y `estaDentro(Socio)`. Los servicios lo reciben por el constructor y los controladores
por parámetro (DIP: inyección de dependencias del contenedor de Laravel). Así queda la cadena
Controlador → Servicio → Repositorio → Modelo que pide Programación IV. El código viejo se borró, y
`AsistenciaRepositoryTest` verifica la regla (incluido el caso del socio que no marcó la salida ayer).

**Consecuencia negativa asumida.**
- Una capa más para leer: quien busca la consulta tiene que abrir el repositorio.
- El repositorio es una clase concreta, sin interfaz. Una interfaz solo se justificaría con una segunda
  implementación (por ejemplo, otra fuente de datos), y hoy no la hay: agregarla sería un cambio imaginario.
- Eloquent ya es en sí un repositorio (Active Record). Por eso no se crea un repositorio por cada tabla: solo
  para las consultas que se repiten.

**Alternativa descartada.** Un *scope* `presentesHoy()` en el modelo `Asistencia` también eliminaba la
duplicación, y con menos código. Se eligió el repositorio porque deja la regla fuera del modelo, porque se puede
inyectar y reemplazar en una prueba, y porque es el patrón que la cátedra pide demostrar.

## 2. Observer — eventos de la pantalla de acceso (HU21)

**Síntoma.** `AccesoService` decide el ingreso y el egreso, pero el sistema necesita enterarse de esas decisiones
en más de un lugar: hoy, para dejar constancia de cada acceso y de cada rechazo; en el Sprint 4, para avisar en
tiempo real al aforo del portal (HU25, Reverb). Hacerlo dentro del servicio lo obligaría a conocer a cada
interesado y a cambiar cada vez que aparece uno nuevo.

**Deuda.** Acoplamiento: el servicio de acceso tendría más de una razón para cambiar (viola SRP) y no se podría
sumar un interesado sin modificar código ya probado (viola OCP). Además, la pantalla de acceso deniega el ingreso
de forma automática y no quedaba registro del motivo, que es lo que se necesita para explicar después esa
decisión (art. 1757 CCyC).

**Las tres preguntas.**
1. ¿Se repite? Hay dos interesados en el mismo hecho: el registro de auditoría (ya implementado) y el aviso en
   tiempo real (planificado en HU25).
2. ¿El cambio es probable? Sí: HU25 ya está en el Sprint 4, y otros interesados posibles son las estadísticas
   o una notificación a recepción.
3. ¿Qué consecuencia negativa introduce? El flujo deja de leerse de corrido (ver abajo).

**Solución.** El servicio es el **sujeto**: solo anuncia lo que pasó con tres eventos (`app/Events`):
`IngresoRegistrado`, `EgresoRegistrado` y `AccesoDenegado` (este último con los motivos). El **observador**
`app/Listeners/RegistrarAccesoEnLog.php` los escucha y escribe una línea en el log de la aplicación. Laravel lo
conecta solo, por descubrimiento de eventos: mira el tipo del parámetro de `handle()` (se puede ver con
`php artisan event:list`). Para sumar otro interesado se crea otro listener y `AccesoService` no se toca (OCP).
El log guarda solo el id del socio, nunca el nombre, el DNI ni el PIN (minimización de datos, Ley 25.326). En
Render el log va a `stderr` y se ve en el panel de logs del servicio.

**Pruebas.** `AccesoEventosTest` usa `Event::fake()` para comprobar que cada caso dispara su evento y que el
listener está registrado para los tres. También usa `Log::shouldReceive()` para comprobar que el rechazo guarda
los motivos y no el DNI. Detalle aprendido: `Event::fake()` sin argumentos también apaga los eventos de modelo
de Eloquent (`Socio::booted()` genera el PIN), por eso se le pasa la lista de eventos a simular.

**Consecuencia negativa asumida.**
- Indirección: leyendo `AccesoService` no se ve quién reacciona; hay que buscar los listeners o usar
  `php artisan event:list`.
- El listener corre en el mismo pedido (sincrónico). Si un día hiciera algo lento, conviene que implemente
  `ShouldQueue` para que vaya a la cola; hoy escribir una línea de log no lo justifica.
- Un log no es una tabla: sirve para auditar, pero no para hacer consultas. Si el gimnasio quisiera reportes de
  rechazos, haría falta persistirlos en la base.
