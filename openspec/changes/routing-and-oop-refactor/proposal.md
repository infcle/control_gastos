# Proposal: Routing & OOP Controller Refactor

## Intent

Eliminar las URLs con parámetros GET (ej: `/controller/user/?action=edit&id=5`) y reemplazar los controladores procedurales con clases orientadas a objetos, implementando un Front Controller + Router para toda la aplicación.

## Scope

### In Scope
- `.htaccess` con mod_rewrite → todo al Front Controller
- `lib/Router.php` — parsea URLs amigables (`/user/edit/5`) y resuelve controller/action/params
- `lib/BaseController.php` — métodos compartidos: `render()`, `redirect()`, `requireAuth()`, `requireRole()`
- `config/routes.php` — tabla de rutas centralizada
- Refactor de 7 controladores a clases OOP (User, Category, Supplier, Purchase, Product, Home, Login)
- Actualizar URLs en todas las vistas (`view/**/*.php`)
- Mantener tests existentes funcionando
- Eliminar archivos `controller/{module}/index.php` viejos

### Out of Scope
- Refactor de Models (ya están en POO)
- Cambios en el sistema de templates (sigue con includes + variables sueltas)
- ORM, autoloader con Composer, o cambios en la capa de datos
- Cambios visuales o de CSS
- Modificar el sistema de autenticación (sigue con sesiones)

## Capabilities

### New Capabilities
None — refactor puro, sin nuevas capacidades a nivel de spec.

### Modified Capabilities
None — ningún comportamiento existente cambia a nivel de especificación.

## Approach

### Fase 1 — Infraestructura
1. Crear `lib/Router.php`: clase que recibe la URI, la parsea contra `config/routes.php` y devuelve controller/method/params
2. Crear `lib/BaseController.php`: clase abstracta con `render($view, $data)`, `redirect($url)`, `requireAuth()`, `requireRole()`
3. Crear `config/routes.php`: array asociativo `'pattern' => 'Controller@method'`
4. Modificar `index.php`: carga el Router, resuelve la ruta, instancia el controller, ejecuta el método
5. Crear `.htaccess`: RewriteRule que envía todo a `index.php`

### Fase 2 — Refactor de Controladores
Cada `controller/{module}/index.php` procedural se convierte en `controller/{Module}Controller.php`:
- Los métodos del switch pasan a ser métodos con nombre (`listAction`, `createAction`, `editAction`, `deleteAction`, etc.)
- Auth checking via `$this->requireAuth()` / `$this->requireRole()` del BaseController
- Render vía `$this->render('module/content-list', ['users' => $users])`
- Redirecciones vía `$this->redirect('user/list')`

### Fase 3 — Actualizar URLs en Vistas
Buscar y reemplazar en todas las vistas:
- `CONTROLLER_URL . 'module/?action=x&id=y'` → `BASE_URL . 'module/x/y'`
- Breadcrumbs, formularios, botones, links de acción

### Fase 4 — Limpieza y Verificación
- Eliminar `controller/{module}/index.php` viejos
- Ejecutar tests
- Verificar navegación manual

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `.htaccess` | New | RewriteRule → index.php |
| `index.php` | Modified | Become Front Controller |
| `config/app_config.php` | Modified | Ajustar URLs |
| `config/routes.php` | New | Definiciones de ruta |
| `lib/Router.php` | New | Parsing de URLs |
| `lib/BaseController.php` | New | Clase base abstracta |
| `controller/UserController.php` | New | Reemplaza user/index.php |
| `controller/CategoryController.php` | New | Reemplaza category/index.php |
| `controller/SupplierController.php` | New | Reemplaza supplier/index.php |
| `controller/PurchaseController.php` | New | Reemplaza purchase/index.php |
| `controller/ProductController.php` | New | Reemplaza product/index.php |
| `controller/HomeController.php` | New | Reemplaza home/index.php |
| `controller/LoginController.php` | New | Reemplaza login/index.php |
| `controller/*/index.php` | Removed | Archivos viejos procedurales |
| `view/**/*.php` | Modified | URLs internas actualizadas |
| `config/auth_helper.php` | Modified | Integrado en BaseController |
| `tests/*.php` | Modified | URLs de prueba actualizadas |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Links rotos en vistas no actualizadas | Medium | grep + test manual de cada módulo |
| Cambio >400 líneas (alta probabilidad) | High | Dividir en PRs encadenados por módulo |
| Archivos controller/*/index.php residuales | Medium | Eliminar commit aparte después de verificar |
| Incompatibilidad con tests existentes | Low | Ejecutar tests después de cada fase |

## Rollback Plan

1. `git revert` del commit del Front Controller + .htaccess
2. Restaurar `index.php` original
3. Eliminar archivos nuevos en `lib/` y `controller/*Controller.php`
4. `git checkout` de todas las vistas modificadas
5. Verificar que URLs originales funcionan

## Dependencies

- Apache mod_rewrite habilitado (WAMP lo tiene por defecto)
- PHP 7.0+

## Success Criteria

- [ ] `Router::resolve('/user/edit/5')` → `['controller' => 'UserController', 'action' => 'edit', 'params' => [5]]`
- [ ] Todos los controladores funcionan como clases OOP
- [ ] Todas las URLs en vistas apuntan a rutas amigables
- [ ] Tests existentes pasan sin modificaciones mayores
- [ ] No hay archivos `controller/{module}/index.php` huérfanos
