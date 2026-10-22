# Deuda técnica y patrones de diseño — Gym Control

Registro que pide Metodología de Sistemas II. Cada entrada sigue el formato **síntoma → deuda → patrón o
solución → consecuencia negativa asumida**, y responde las tres preguntas que el equipo se hace antes de aplicar
un patrón: 1) ¿el problema ya apareció 2 o 3 veces?, 2) ¿el cambio es probable o imaginario?, 3) ¿qué
consecuencia negativa introduce?

| # | Patrón | Historia | Estado |
|---|---|---|---|
| 1 | Repository | HU23 | Aplicado |
| 2 | Observer | HU21 | Pendiente (Sprint 4) |
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
