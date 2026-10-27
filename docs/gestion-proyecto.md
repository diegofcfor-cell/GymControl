# Gestión del proyecto: Scrum con tablero Kanban en GitHub Projects

**Proyecto:** Gym Control, Grupo 13 (Cardozo, Heretichi, López).
**Vigente desde:** 27/10/2026, inicio del Sprint 2 del Plan de Trabajo.
**Asignaturas:** Gestión de Desarrollo de Software (modelo de proceso, Scrum/Kanban, KPIs) y Metodología de
Sistemas II (Git, pull requests, CI).

## 1. Metodología

El Plan de Trabajo aprobado (sección 12) define una metodología ágil basada en Scrum: Product Backlog, historias de
usuario, sprints de dos semanas y un tablero Kanban con las columnas To Do, In Progress, In Review y Done. Esa
metodología no cambia. Lo que cambia es la herramienta que soporta el tablero.

| Rol de Scrum | Persona |
|---|---|
| Product Owner | Viviana Machuca (tutora del gimnasio) |
| Scrum Master | Heretichi, Adriana |
| Equipo de desarrollo | Cardozo, Heretichi y López |

**Definición de hecho** (Plan, sección 12): la historia está implementada, probada, integrada en `main` mediante un
pull request y verificada contra sus criterios de aceptación.

## 2. Cambio de herramienta: de Jira a GitHub Projects

El Plan nombra Jira como tablero. A partir del Sprint 2 el equipo lo reemplaza por GitHub Projects.

**Motivo: trazabilidad.** La norma IEEE 830 pide que cada requerimiento se pueda seguir hasta su implementación y su
prueba. En Jira ese vínculo con el código se arma a mano o con una integración externa. En GitHub Projects la tarjeta
del tablero es el mismo issue del repositorio, de modo que la cadena queda registrada sin trabajo adicional:

```
RF (docs/plan-actualizacion.md) → HU (issue) → rama → commits → pull request → CI (pruebas) → merge → Done
```

Un pull request que incluye `Closes #N` cierra el issue al mergearse, y la automatización del tablero mueve la tarjeta
a *Done*. El estado del tablero deja de depender de que alguien se acuerde de actualizarlo.

| Criterio | Jira (Plan) | GitHub Projects (actual) |
|---|---|---|
| Tablero Kanban y sprints | Sí | Sí (vista *Board* y campo *Iteration*) |
| Gantt | Sí (*Timeline*) | Sí (vista *Roadmap*) |
| Vínculo con el código | Integración externa | Nativo: issue, rama, PR y CI en la misma tarjeta |
| Herramientas que usa el equipo | Dos (Jira y GitHub) | Una (GitHub) |

**Consecuencia negativa asumida:** GitHub Projects tiene menos informes de agilidad que Jira (no trae un burndown
listo). Se compensa con la vista *Insights* (gráfico de historias por estado e iteración) y con la planilla de Gantt.

## 3. Cómo se implementó

1. **Backlog completo como issues.** Cada historia de usuario (HU1 a HU33) es un issue con el formato
   `HUnn · título`, la historia en la forma «Como … quiero … para …», los criterios de aceptación como casillas y una
   sección de trazabilidad con el RF, el código y la prueba. Las historias ya implementadas en v1.0.0 se cargaron
   cerradas como completadas (#59 a #80, más las que ya existían: #25 a #47).
2. **Tareas de los sprints que quedan.** Las tareas de los sprints 2 a 4 (diagramas, pruebas de aceptación, video,
   informe y defensa) se cargaron abiertas (#81 a #89), también como historias con criterios de aceptación.
3. **Plantillas.** `.github/ISSUE_TEMPLATE/historia-de-usuario.yml` obliga a completar la historia, los criterios,
   el RF y la prioridad al crear un issue nuevo. `.github/pull_request_template.md` recuerda enlazar el issue con
   `Closes #N` y marcar la definición de hecho.
4. **Tablero.** Un proyecto de GitHub Projects vinculado al repositorio, con estas columnas y campos:

| Elemento | Configuración |
|---|---|
| Columnas (campo *Status*) | Backlog, To Do, In Progress, In Review, Done |
| Campo *Iteration* | Iteraciones de 2 semanas: Sprint 2 (26/10–06/11), Sprint 3 (09/11–20/11), Sprint 4 (23/11–04/12) |
| Campo *Prioridad* | Esencial, Útil, Deseable |
| Campo *Horas* | Número: estimación para comparar con las horas del Plan |
| Vistas | *Board* (Kanban), *Table* (backlog) y *Roadmap* (Gantt) |
| Automatizaciones | *Item added* → Backlog; *Item closed* y *Pull request merged* → Done; *Auto-add* de los issues del repositorio |
| Límite WIP | 1 tarjeta por persona en In Progress; 3 en In Review |

## 4. Convenciones

| Elemento | Formato | Ejemplo |
|---|---|---|
| Issue de historia | `HUnn · título` | `HU14 · Consultar el aforo antes de ir` |
| Issue de tarea | `Sprint N · título` | `Sprint 3 · Video explicativo en inglés` |
| Etiquetas | `historia-de-usuario`, `tarea`, `ampliacion`, `prioridad-alta`, `prioridad-media`, `docs`, `pruebas`, `presentacion`, `fix` | — |
| Rama | `tipo/huNN-tema` | `feat/hu14-aforo` |
| Commit | `tipo(ámbito): descripción` | `feat(aforo): nivel libre, moderado o lleno` |
| Pull request | Cuerpo con `Closes #N` | `Closes #72` |
| Versión | Release con tag `vX.Y.Z` | `v1.0.0` |

## 5. Seguimiento e indicadores

| Indicador | Cómo se obtiene | Para qué sirve |
|---|---|---|
| Velocidad | Historias o horas en Done por iteración (*Insights*) | Planificar el sprint siguiente con datos |
| Cumplimiento de hitos | Fecha planificada contra fecha real de cada sprint (Gantt) | Detectar desvíos de la ruta crítica del PERT |
| Densidad de defectos | Issues `fix` por módulo | Capítulo de resultados y pruebas del informe |
| Trabajo en curso | Tarjetas en In Progress | Respetar el límite WIP |

Ceremonias: planificación el lunes de inicio de sprint, seguimiento breve en cada jornada (un comentario en la
tarjeta: qué hice, qué haré, qué me bloquea), revisión con la Product Owner el viernes de cierre y retrospectiva
después de la revisión.
