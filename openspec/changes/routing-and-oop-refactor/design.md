# Design: Routing & OOP Controller Refactor

## Technical Approach

Refactor progresivo: se agrega infraestructura de routing sin romper el funcionamiento actual, luego se migran los controladores uno por uno, y finalmente se actualizan las vistas y se eliminan los archivos viejos. El template system existente (variables sueltas + layout.php con includes) se conserva intacto.

## Architecture Decisions

### Decision: Patrón de routing

| Opción | Tradeoff | Decisión |
|--------|----------|----------|
| Front Controller + Router class | Una sola entrada, control total, fácil de debuguear | ✅ Elegido |
| .htaccess per-module | Más archivos, disperso, difícil de mantener | ❌ Rechazado |
| MVC framework externo | Demasiado cambio, rompe todo | ❌ Rechazado |

**Rationale**: El Front Controller es el estándar en PHP MVC. Se implementa un Router liviano sin dependencias externas.

### Decision: Convención de naming de rutas

```
/{controller}/{action}/{params}
/user/edit/5  →  UserController::editAction(5)
/category     →  CategoryController::listAction()
```

**Rationale**: Sigue la convención RESTful básica. Las rutas se definen en `config/routes.php` como expresiones regulares.

### Decision: BaseController con métodos de instancia

```php
abstract class BaseController {
    protected function render(string $view, array $data = []): void
    protected function redirect(string $path): void
    protected function requireAuth(): void
    protected function requireRole(string $role): void
    protected function getAuthUser(): array|null
}
```

**Rationale**: Centraliza auth, render y redirect. Los controladores concretos heredan y agregan su lógica específica. `render()` extrae `$data` como variables sueltas para mantener compatibilidad con las vistas existentes.

### Decision: Sin autoloader (por ahora)

**Rationale**: El proyecto no usa Composer ni namespace. Se usarán `require_once` tradicionales en el Front Controller. Migrar a autoloader queda fuera de scope.

## Data Flow

```
Navegador → /user/edit/5
                ↓
          .htaccess (RewriteRule)
                ↓
          index.php (Front Controller)
                ↓
          Router::resolve('/user/edit/5')
                ↓
          config/routes.php → match 'user/edit/:id'
                ↓
          new UserController($db)
                ↓
          $controller->editAction(5)
                ↓
          $this->render('user/content-form', ['userData' => $data])
                ↓
          extract($data) + require VIEW_PATH . 'template/layout.php'
                ↓
          HTML response
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `index.php` | Modify | Front Controller: carga Router, resuelve ruta, ejecuta controller |
| `.htaccess` | Create | RewriteRule: todo a index.php |
| `config/app_config.php` | Modify | Agregar ruta base, ajustar URLs |
| `config/routes.php` | Create | Tabla de rutas con patterns → Controller@method |
| `lib/Router.php` | Create | Clase Router: `resolve($uri)`, `generate($name, $params)` |
| `lib/BaseController.php` | Create | Clase abstracta con métodos compartidos |
| `controller/UserController.php` | Create | Reemplaza `controller/user/index.php` |
| `controller/CategoryController.php` | Create | Reemplaza `controller/category/index.php` |
| `controller/SupplierController.php` | Create | Reemplaza `controller/supplier/index.php` |
| `controller/PurchaseController.php` | Create | Reemplaza `controller/purchase/index.php` |
| `controller/ProductController.php` | Create | Reemplaza `controller/product/index.php` |
| `controller/HomeController.php` | Create | Reemplaza `controller/home/index.php` |
| `controller/LoginController.php` | Create | Reemplaza `controller/login/index.php` |
| `controller/*/index.php` | Delete | Archivos procedurales viejos |
| `view/**/*.php` | Modify | Actualizar URLs a rutas amigables |
| `config/auth_helper.php` | Delete | Funcionalidad migrada a BaseController |
| `tests/*.php` | Modify | URLs de prueba actualizadas |

## Interfaces / Contracts

### Router::resolve()

```php
public function resolve(string $uri): array
// Returns: ['controller' => 'UserController', 'action' => 'edit', 'params' => [5]]
```

### BaseController::render()

```php
protected function render(string $view, array $data = []): void
// $view: ruta relativa a VIEW_PATH sin extensión, ej: 'user/content-list'
// $data: array que se extrae como variables sueltas para la vista
```

### Route definition format (config/routes.php)

```php
return [
    '/'                    => ['HomeController', 'indexAction'],
    'login'                => ['LoginController', 'indexAction'],
    'login/logout'         => ['LoginController', 'logoutAction'],
    'user'                 => ['UserController', 'listAction'],
    'user/create'          => ['UserController', 'createAction'],
    'user/edit/:id'        => ['UserController', 'editAction'],
    'user/delete/:id'      => ['UserController', 'deleteAction'],
    'user/toggle-status/:id' => ['UserController', 'toggleStatusAction'],
    'user/change-password/:id' => ['UserController', 'changePasswordAction'],
    'user/profile'         => ['UserController', 'profileAction'],
    'category'             => ['CategoryController', 'listAction'],
    // ... similar for supplier, purchase, product
];
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | Router::resolve() con varias URLs | Test manual o script simple |
| Integration | Cada controlador responde 200 | Tests existentes (se actualizan URLs) |
| E2E | Navegación completa | Tests existentes + navegación manual |

Los tests existentes (`tests/*Test.php`) se actualizan para usar las nuevas URLs pero la lógica de negocio probada es la misma.

## Migration / Rollout

No migration required. El cambio es puramente en la capa de presentación/ruteo. Los modelos y la DB no cambian.

Se recomienda implementar en este orden:
1. `.htaccess` + `config/routes.php` + `lib/Router.php` + `lib/BaseController.php` + `index.php` modificado
2. Migrar controladores uno por uno (login → home → user → category → supplier → product → purchase)
3. Actualizar vistas
4. Eliminar archivos viejos
5. Tests

## Open Questions

- [ ] ¿Mantener `CONTROLLER_URL` constante o eliminarla? (Respuesta: mantenerla por compatibilidad pero dejar de usarla en nuevas URLs)
- [ ] ¿El Router debe soportar parámetros query string además de path? (Respuesta: sí, `$_GET` sigue disponible para casos como paginación)
