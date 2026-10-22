# Registro de cambios

Todas las versiones de Gym Control siguen el versionado semántico (`MAJOR.MINOR.PATCH`). Este archivo lo
actualiza el workflow `.github/workflows/update-changelog.yml` cada vez que se publica un Release en GitHub:
copia las notas del Release debajo de *Unreleased*. No hace falta editarlo a mano.

## [Unreleased](https://github.com/diegofcfor-cell/GymControl/compare/v1.0.0...main)

## [v1.0.0](https://github.com/diegofcfor-cell/GymControl/compare/v1.0.0...v1.0.0) - 2026-10-22

Gym Control v1.0.0
Primera versión estable del sistema de gestión y control de socios del Gimnasio Fénix (TIF, Grupo 13).

Alcance del Plan de Trabajo aprobado
HU1 a HU12: login y roles, operadores, rutas protegidas, socios con buscador, membresías, aptos físicos, pagos y vencimientos, PIN y QR, acceso por PIN y por QR, historial en PDF y dashboard.
Funciones agregadas después del Plan
Panel de recepción (HU29), portal del socio, aforo en tiempo real con Reverb (HU25), chatbot de preguntas frecuentes, rutinas (biblioteca R01–R15), API REST con Sanctum y pedidos de rectificación o supresión de datos (HU27).
Calidad y despliegue
132 pruebas PHPUnit (18 unitarias y 114 de integración) y Pint en CI.
Patrones Repository, Observer, Strategy y Facade documentados en docs/DEUDA-TECNICA.md.
Manual de usuario con capturas en docs/manual/manual-usuario.md.
Producción en Render con MySQL 8 persistente en Aiven (TLS) y procedimiento de respaldo probado (RNF03).
