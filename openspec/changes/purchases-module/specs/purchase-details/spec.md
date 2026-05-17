# Purchase Details Specification

## Purpose

Line items within a purchase trip. Each detail records which product was bought from which supplier, the quantity, the unit price paid, and an optional observation. Details are created and deleted alongside their parent purchase.

## Requirements

### REQ-PDT-001: Create Detail (Inline)

The system **MUST** accept `id_product`, `id_supplier`, `quantity`, `unit_price`, and optional `observation` when creating a purchase. Multiple details from different suppliers **MUST** be accepted in a single request.

#### Scenario: Multiple suppliers in one purchase

- GIVEN a purchase form with 2 details from "Distribuidora Norte" and 1 detail from "Distribuidora Sur"
- WHEN the purchase create action is submitted
- THEN all 3 details are inserted with the same `id_purchase`
- AND each detail correctly references its respective supplier

#### Scenario: Single detail

- GIVEN a purchase form with 1 detail (product=5, qty=2.5, price=150.00)
- WHEN submitted
- THEN exactly 1 detail row is inserted
- AND `quantity`=2.5, `unit_price`=150.00 are stored correctly

### REQ-PDT-002: Validate Detail Fields

The system **MUST** validate quantity > 0, unit_price >= 0, and require valid FK references to existing active products and suppliers.

#### Scenario: Zero quantity rejected

- GIVEN a detail with quantity=0
- WHEN the purchase create is submitted
- THEN the system rejects with "Quantity must be greater than zero"

#### Scenario: Invalid product FK

- GIVEN a detail with `id_product=999` (does not exist)
- WHEN submitted
- THEN the system rejects with "Product not found"

#### Scenario: Invalid supplier FK

- GIVEN a detail with `id_supplier=999` (does not exist)
- WHEN submitted
- THEN the system rejects with "Supplier not found"

### REQ-PDT-003: List Details by Purchase

The system **MUST** return all active details for a given purchase, including product name and supplier name via JOINs.

#### Scenario: Purchase with details

- GIVEN a purchase `id_purchase=5` with 2 active details
- WHEN listing details for purchase 5
- THEN both details are returned with product name, supplier name, quantity, and unit price

#### Scenario: Details soft-deleted

- GIVEN a purchase where details were soft-deleted
- WHEN listing details for that purchase
- THEN an empty result set is returned

### REQ-PDT-004: Soft Delete Detail

The system **MUST** soft-delete purchase-details by setting `deleted_at`. This is triggered by the parent purchase delete (cascade) or by a standalone delete action.

#### Scenario: Cascade from purchase delete

- GIVEN a purchase with 3 active details
- WHEN the purchase is soft-deleted
- THEN all 3 details have `deleted_at` set
- AND the details no longer appear in detail lists

### REQ-PDT-005: Auth Guard

All purchase-detail actions **MUST** require an active Administrator session.

#### Scenario: Unauthenticated access

- GIVEN no active session
- WHEN accessing any detail action
- THEN the system redirects to login
