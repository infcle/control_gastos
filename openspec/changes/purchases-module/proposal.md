# Proposal: Purchases Module

## Intent

Record real-world shopping trips: what products were bought, from which supplier, at what price, when, and by whom. Not an accounting document — tracks purchases for later reporting on categories and supplier pricing.

## Scope

### In Scope

- DB migrations: `categories`, `suppliers`, `purchases`, `purchase_details` tables + `id_category` on `products`
- MVC modules: suppliers CRUD, categories CRUD, purchases with inline detail entry
- Soft delete on all new entities, sidebar nav integration

### Out of Scope

- Budgets, expense limits, charts, PDF export
- Purchase editing (only create/delete for v1)
- Stock/inventory, auto-price-capture from details

## Capabilities

### New Capabilities

- `suppliers`: CRUD for name + location
- `categories`: CRUD for product classification
- `purchases`: create trip header (date, user, observation)
- `purchase-details`: line items per supplier (product, qty, unit_price, observation)

### Modified Capabilities

- None — first SDD cycle

## Approach

- Follow existing MVC: `model/` (mysqli, real_escape_string), `controller/?action=` routing, `view/` (Bootstrap 5, htmlspecialchars)
- 4 sequential SQL migrations in `database/migrations/`
- Product model gains `id_category` + join to categories
- Sidebar nav in `view/template/partials/aside.php`

## Affected Areas

| Area | Impact |
|------|--------|
| `database/migrations/` | New — 4 migration files |
| `model/supplier/`, `model/category/`, `model/purchase/` | New — model classes |
| `controller/supplier/`, `controller/category/`, `controller/purchase/` | New — controllers |
| `view/supplier/`, `view/category/`, `view/purchase/` | New — view files |
| `model/product/Product.php` | Modified — add category field |
| `view/template/partials/aside.php` | Modified — nav entries |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Data integrity across purchase–detail–supplier | Med | FK constraints + transactions |
| Migration order (categories before FK) | Low | Numbered sequential migrations |
| Purchase form complexity | Med | Validate server-side; group UI per supplier |

## Rollback

1. Remove nav links from `aside.php`
2. Delete `controller/{supplier,category,purchase}/`
3. Delete `model/{supplier,category,purchase}/` and `view/{supplier,category,purchase}/`
4. Run: `DROP TABLE purchase_details, purchases, suppliers, categories`
5. Run: `ALTER TABLE products DROP COLUMN id_category`

## Success Criteria

- [ ] Create category and assign it to a product via product form
- [ ] CRUD suppliers with name + location
- [ ] Create purchase with multiple suppliers and line items
- [ ] All CRUD uses soft delete + auth guard
- [ ] Existing product CRUD remains backward compatible
