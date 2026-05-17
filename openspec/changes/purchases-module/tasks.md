# Tasks: Purchases Module

## Review Workload Forecast

Decision needed before apply: No  (auto-chain — user already chose chained PRs)
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Work Units by PR

| PR | Content | Base Branch | Lines Est. |
|----|---------|-------------|------------|
| 1 | Categories + Suppliers CRUD + tests | feature/purchases-module | ~450 |
| 2 | Product category field + Purchases model/controller/views | PR #1 branch | ~550 |
| 3 | Purchase details integration + PurchaseTest | PR #2 branch | ~200 |

## Phase 1: Foundation (PR 1)

- [x] 1.1 Create `script_db/17_05_2026_create_categories.sql` — categories table with id_category, name, description, deleted_at
- [x] 1.2 Create `script_db/17_05_2026_create_suppliers.sql` — suppliers table with id_supplier, name, location, deleted_at
- [x] 1.3 Run both migrations against dev DB

## Phase 2: Categories CRUD (PR 1)

- [x] 2.1 Create `model/category/Category.php` — CRUD + soft delete, unique name check, FK guard for in-use categories
- [x] 2.2 Create `controller/category/index.php` — auth guard, switch on ?action=, set $pageTitle/$breadcrumb/$content
- [x] 2.3 Create `view/category/content-list.php` — Bootstrap table with edit/delete actions
- [x] 2.4 Create `view/category/content-form.php` — form for name + description, used by create/edit

## Phase 3: Suppliers CRUD (PR 1)

- [x] 3.1 Create `model/supplier/Supplier.php` — CRUD + soft delete, unique name check
- [x] 3.2 Create `controller/supplier/index.php` — same pattern as category controller
- [x] 3.3 Create `view/supplier/content-list.php` — Bootstrap table with edit/delete
- [x] 3.4 Create `view/supplier/content-form.php` — two-field form (name, location)

## Phase 4: Navigation (PR 1)

- [x] 4.1 Modify `view/template/partials/aside.php` — add Categories and Suppliers nav items before Products

## Phase 5: Product Category Field (PR 2)

- [x] 5.1 Create `script_db/17_05_2026_add_category_to_products.sql` — ALTER TABLE products ADD id_category INT NULL
- [x] 5.2 Modify `model/product/Product.php` — add id_category to all SELECT queries, add getAllCategories() method
- [x] 5.3 Modify `view/product/content-list.php` — add category column to table
- [x] 5.4 Modify `view/product/content-form.php` — add category dropdown select

## Phase 6: Purchases Foundation (PR 2)

- [x] 6.1 Create `script_db/17_05_2026_create_purchases.sql` — purchases + purchase_details with FK constraints, deleted_at, indexes

## Phase 7: Purchases CRUD (PR 2)

- [x] 7.1 Create `model/purchase/Purchase.php` — CRUD, transaction-based create with details, cascade soft delete
- [x] 7.2 Create `controller/purchase/index.php` — auth guard, validate details, begin/commit transaction
- [x] 7.3 Create `view/purchase/content-list.php` — purchase list with date, user name, observation
- [x] 7.4 Create `view/purchase/content-form.php` — **key view**: JS dynamic rows grouped by supplier, nested `suppliers[]` POST format

## Phase 8: Navigation + Testing (PR 2/PR 3)

- [x] 8.1 Modify `view/template/partials/aside.php` — add Purchases nav item
- [ ] 8.2 Create `tests/CategoryTest.php` — CLI test: create, list, update, soft delete, FK guard
- [ ] 8.3 Create `tests/SupplierTest.php` — CLI test: create, list, update, soft delete, unique name
- [ ] 8.4 Create `tests/PurchaseTest.php` — CLI test: create with 2 suppliers, verify DB state, cascade soft delete
- [ ] 8.5 Run all 3 tests + existing ProductTest to confirm no regressions
