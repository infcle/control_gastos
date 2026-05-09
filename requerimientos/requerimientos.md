# Requerimientos del Sistema de Control de Gastos

Basándonos en la descripción del sistema, aquí se detallan los requerimientos funcionales y no funcionales para el control de gastos.

## Requerimientos Funcionales

1. **Registro de Gastos**: Permitir al usuario registrar gastos con detalles como fecha, monto, descripción, producto(s) involucrado(s), proveedor y categoría.
2. **Gestión de Productos**: Crear, editar y listar productos con información como nombre, precio actual, historial de precios (para rastrear cambios a lo largo del tiempo), y clasificación por categorías.
3. **Gestión de Proveedores**: Registrar proveedores con datos como nombre, contacto y productos asociados.
4. **Clasificación de Productos**: Asignar categorías a productos (ej. "Alimentos", "Transporte", "Entretenimiento") para organizar y analizar gastos.
5. **Presupuestos por Categoría**: Definir presupuestos mensuales/anuales por categoría y comparar con gastos reales, generando alertas si se supera el límite.
6. **Historial y Reportes**: Mostrar historial de precios de productos, gastos por período, y reportes resumidos (ej. total por categoría, proveedor o fecha).
7. **Autenticación y Roles**: Usuarios con roles (ej. admin para gestionar todo, usuario básico para registrar gastos), integrando el login existente.
8. **Interfaz de Usuario**: Vistas responsivas para registro, listas y reportes, usando el framework de vistas actual (ej. Bootstrap en `view/`).
9. **Base de Datos**: Tablas para gastos, productos, proveedores, categorías y usuarios, con relaciones (ej. gasto -> producto -> categoría).

## Requerimientos No Funcionales

- **Seguridad**: Validación de datos, protección contra inyección SQL.
- **Rendimiento**: Consultas eficientes para reportes.
- **Usabilidad**: Interfaz intuitiva, compatible con móviles.
- **Escalabilidad**: Soporte para múltiples usuarios y datos históricos.