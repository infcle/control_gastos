# Tareas del Proyecto de Control de Gastos (Monolítico)

Este proyecto es monolítico (una sola aplicación PHP con MVC), basado en los requerimientos y historias de usuario. Las tareas están desglosadas por fases para implementación incremental.

## Fase 1: Base de Datos y Configuración Inicial

1. **Crear tablas en MySQL**: Implementar script para `gastos`, `productos`, `proveedores`, `categorias`, `usuarios` con relaciones (FK). Usar `script_db/`.
2. **Configurar conexión DB**: Actualizar `config/database.php` con credenciales y PDO.
3. **Migraciones iniciales**: Ejecutar scripts de DB y verificar integridad.

## Fase 2: Backend (Modelos y Controladores)

1. **Modelo Usuario**: Crear `model/User.php` con métodos CRUD (login, registro).
2. **Modelo Producto**: Crear `model/Product.php` con historial de precios y categorías.
3. **Modelo Gasto**: Crear `model/Gasto.php` para registrar y consultar gastos.
4. **Modelo Proveedor**: Crear `model/Proveedor.php` con vinculación a productos.
5. **Modelo Categoría**: Crear `model/Categoria.php` para presupuestos.
6. **Controladores**: Actualizar `controller/` para manejar acciones (ej. `product/index.php` para CRUD productos).
7. **Autenticación**: Implementar login/logout en `controller/login/index.php` con sesiones.

## Fase 3: Frontend (Vistas y UI)

1. **Vista de registro de gastos**: Crear formulario en `view/product/content-form.php` con campos fecha, monto, producto, etc.
2. **Vista de lista de productos**: Actualizar `view/product/content-list.php` con tabla y filtros.
3. **Vista de presupuestos**: Crear nueva vista en `view/` para categorías con alertas.
4. **Vista de reportes**: Implementar gráficos (usando JS en `assets/js/`) para historial y totales.
5. **UI responsiva**: Actualizar CSS en `view/assets/css/` para móviles.

## Fase 4: Lógica de Negocio y Validaciones

1. **Validaciones**: Agregar checks en controladores (ej. CSRF, sanitización de inputs).
2. **Presupuestos**: Lógica para calcular límites por categoría y notificaciones.
3. **Historial de precios**: Implementar tracking en modelo Producto.
4. **Roles de usuario**: Diferenciar admin/usuario en vistas y permisos.

## Fase 5: Testing y QA

1. **Tests unitarios**: Ejecutar y expandir `tests/` (ej. `ProductTest.php`).
2. **Tests de integración**: Verificar DB y controladores.
3. **Testing manual**: Validar flujos de usuario (registro de gasto, reportes).

## Fase 6: CI/CD y Despliegue

1. **Configurar GitHub Actions**: Usar workflows en `.github/workflows/` para tests automáticos.
2. **Despliegue local**: Probar en WAMP.
3. **Migración a hosting**: Preparar para servidor (ej. exportar DB).

Prioridad: Comenzar con DB y modelos. Estimación total: 4-6 semanas para un desarrollador. ¿Quieres desglosar alguna tarea?
