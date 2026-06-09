# Tasks: Routing & OOP Controller Refactor

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~800-1200 (additions + deletions) |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Suggested split | PR1: Infrastructure → PR2: Controllers → PR3: Views cleanup |
| Delivery strategy | ask-on-risk |
| Chain strategy | pending |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: pending
400-line budget risk: High

## Suggested Work Units

| Unit | Goal | Likely PR | Notes |
|------|------|-----------|-------|
| 1 | Infrastructure: Router + BaseController + .htaccess + Front Controller + routes | PR 1 | Base para todo lo demás. Incluye tests del Router. |
| 2 | Controllers: Migrar login, home, user, category, supplier, product, purchase | PR 2 | Depende de PR1. Cada controller se migra uno por uno. |
| 3 | Vistas + limpieza: Actualizar URLs en vistas, eliminar archivos viejos | PR 3 | Depende de PR2. grep + replace + delete. |

## Phase 1: Infrastructure

- [x] 1.1 Crear `lib/Router.php` con método `resolve($uri)` que parsea URLs y extrae controller/action/params
- [x] 1.2 Crear `lib/BaseController.php` con `render()`, `redirect()`, `requireAuth()`, `requireRole()`
- [x] 1.3 Crear `config/routes.php` con tabla de todas las rutas de la aplicación
- [x] 1.4 Modificar `index.php` como Front Controller: carga autoload, resuelve ruta, ejecuta controller
- [x] 1.5 Crear `.htaccess` con mod_rewrite: RewriteRule todo a index.php
- [ ] 1.6 Modificar `config/app_config.php`: ajustar/agregar constantes URL (deferred — no necesario para funcionamiento actual)

## Phase 2: Controller Refactor

- [x] 2.1 Crear `controller/LoginController.php`: login, logout como métodos
- [x] 2.2 Crear `controller/HomeController.php`: indexAction con dashboard data
- [x] 2.3 Crear `controller/UserController.php`: list, create, edit, delete, toggleStatus, changePassword, profile
- [x] 2.4 Crear `controller/CategoryController.php`: list, create, edit, delete
- [x] 2.5 Crear `controller/SupplierController.php`: list, create, edit, delete
- [x] 2.6 Crear `controller/ProductController.php`: list, create, edit, delete, toggleStatus, priceHistory
- [x] 2.7 Crear `controller/PurchaseController.php`: list, create, view, delete
- [x] 2.8 Integrar auth_helper en BaseController y eliminar `config/auth_helper.php` (auth_helper.php no existe — ya está integrado)

## Phase 3: Views & Cleanup

- [ ] 3.1 Actualizar URLs en `view/user/content-list.php` y `content-form.php`
- [ ] 3.2 Actualizar URLs en `view/category/content-list.php` y `content-form.php`
- [ ] 3.3 Actualizar URLs en `view/supplier/content-list.php` y `content-form.php`
- [ ] 3.4 Actualizar URLs en `view/product/content-list.php` y `content-form.php`
- [ ] 3.5 Actualizar URLs en `view/purchase/content-list.php`, `content-form.php`, `content-view.php`
- [ ] 3.6 Actualizar URLs en `view/template/partials/aside.php`, `nav-bar.php`, `breadcrumb.php`
- [ ] 3.7 Eliminar archivos `controller/*/index.php` viejos
- [ ] 3.8 Actualizar URLs en `tests/*.php`

## Phase 4: Verification

- [ ] 4.1 Ejecutar todos los tests existentes y corregir fallos
- [ ] 4.2 Verificar navegación manual de cada módulo
- [ ] 4.3 Verificar que no haya links rotos (grep de URLs viejas)
