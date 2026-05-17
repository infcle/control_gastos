# Design: Purchases Module

## Technical Approach

Follow existing MVC patterns: 4 new tables (`categories`, `suppliers`, `purchases`, `purchase_details`), 4 date-prefixed SQL migrations in `script_db/`, 3 new model classes, 3 new controllers, 6 new views. The critical complexity is the purchase form — a single POST submission with multiple products from multiple suppliers. We handle it client-side (JS groups rows by supplier with hidden `id_supplier`) and server-side (single `mysqli::begin_transaction` loop).

## Architecture Decisions

### Decision: Purchase form UX — dynamic JS rows grouped by supplier

| Option | Tradeoff |
|--------|----------|
| A: Single flat list of line items | Simple JS, but no supplier grouping — harder to audit |
| B: Grouped by supplier (chosen) | More complex JS, but matches real shopping trips; each group header shows supplier select, rows below are products for that supplier |
| **Decision**: B. Each "supplier group" is a JS template cloned on click. The server receives `suppliers[]` array with nested `products[][]`. Post-submit, server iterates suppliers, creates details. |

### Decision: Price tracking — snapshot at purchase time

| Option | Tradeoff |
|--------|----------|
| A: FK to `prices` table | Price changes alter historical purchase value |
| B: `unit_price` stored in `purchase_details` (chosen) | Denormalized but immutable; reflects what was actually paid |
| **Decision**: B. `unit_price DECIMAL(10,2)` in each detail row. No FK to prices — intentional snapshot. |

### Decision: Soft delete strategy

| Option | Tradeoff |
|--------|----------|
| A: `deleted_at` on all tables (chosen) | Matches existing `products` pattern; cascade in application code |
| B: `status` field | Inconsistent with products; two conventions in one project |
| **Decision**: A. All 4 new tables get `deleted_at TIMESTAMP NULL DEFAULT NULL`. Cascade: deleting a purchase also sets `deleted_at` on its details (application-level, not DB trigger). |

### Decision: Migration directory

| Option | Tradeoff |
|--------|----------|
| A: New `database/migrations/` per proposal | Breaks existing pattern; two migration locations |
| B: `script_db/` (chosen) | Existing location; `07_05_2026_*.sql` precedent |
| **Decision**: B. Files: `17_05_2026_create_categories.sql`, `17_05_2026_create_suppliers.sql`, `17_05_2026_add_category_to_products.sql`, `17_05_2026_create_purchases.sql`. Run in order — categories before products FK. |

## Data Flow

```
Create Purchase flow:

  Browser (JS groups by supplier)
       │ POST /controller/purchase/?action=create
       ▼
  controller/purchase/index.php
       │ authenticate → check $_SESSION['rol'] == 'Administrator'
       │ validate: date, qty>0, unit_price>=0, FK existence
       │ mysqli::begin_transaction()
       ▼
  model/purchase/Purchase.php
       │ INSERT into purchases (id_user, purchase_date, observation)
       │ INSERT into purchase_details (id_purchase, id_product, id_supplier, qty, unit_price)
       │ mysqli::commit()
       ▼
  Redirect to list view with success message
```

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `script_db/17_05_2026_create_categories.sql` | Create | `categories` table: `id_category`, `name`, `description`, `deleted_at` |
| `script_db/17_05_2026_create_suppliers.sql` | Create | `suppliers` table: `id_supplier`, `name`, `location`, `deleted_at` |
| `script_db/17_05_2026_add_category_to_products.sql` | Create | `ALTER TABLE products ADD COLUMN id_category INT NULL` |
| `script_db/17_05_2026_create_purchases.sql` | Create | `purchases` + `purchase_details` with FK constraints |
| `model/category/Category.php` | Create | CRUD + soft delete, unique name check, protect-delete-if-in-use |
| `model/supplier/Supplier.php` | Create | CRUD + soft delete, unique name check |
| `model/purchase/Purchase.php` | Create | CRUD + transaction-based create with details, cascade soft delete |
| `model/product/Product.php` | Modify | Add `id_category` to ALL queries, add `getAllCategories()` method |
| `controller/category/index.php` | Create | auth guard, switch on `?action=`, set `$content` |
| `controller/supplier/index.php` | Create | Same pattern as category |
| `controller/purchase/index.php` | Create | auth guard, detail validation, begin/commit transaction |
| `view/category/content-list.php` | Create | Bootstrap table with edit/delete actions |
| `view/category/content-form.php` | Create | Bootstrap form for create/edit |
| `view/supplier/content-list.php` | Create | Same pattern as category list |
| `view/supplier/content-form.php` | Create | Two-field form (name, location) |
| `view/purchase/content-list.php` | Create | Purchase list with date, user, observation |
| `view/purchase/content-form.php` | Create | **Key view** — supplier-grouped JS dynamic rows |
| `view/template/partials/aside.php` | Modify | Add nav items: Categories, Suppliers, Purchases (before Products) |
| `tests/CategoryTest.php` | Create | CLI test class |
| `tests/SupplierTest.php` | Create | CLI test class |
| `tests/PurchaseTest.php` | Create | CLI test class |

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Integration | Category CRUD + soft delete + prevent delete-in-use | `php tests/CategoryTest.php` — create, list, update, soft delete, verify `deleted_at`, verify FK guard |
| Integration | Supplier CRUD + soft delete + unique name | `php tests/SupplierTest.php` — same pattern as ProductTest |
| Integration | Purchase create with details + transaction rollback | `php tests/PurchaseTest.php` — create purchase with 2 suppliers, verify DB state, verify cascade delete sets `deleted_at` on details |

## Migration / Rollout

Run migrations in order:
1. `script_db/17_05_2026_create_categories.sql`
2. `script_db/17_05_2026_create_suppliers.sql`
3. `script_db/17_05_2026_add_category_to_products.sql`
4. `script_db/17_05_2026_create_purchases.sql`

No data migration required. Existing products get `id_category = NULL`.

## Open Questions

None — all decisions resolved against codebase patterns.
