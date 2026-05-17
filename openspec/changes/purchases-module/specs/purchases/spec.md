# Purchases Specification

## Purpose

Record shopping trip headers — each purchase captures when (date), who (user), and a general observation. A purchase groups multiple detail lines from one or more suppliers.

## Requirements

### REQ-PUR-001: Create Purchase

The system **MUST** accept `purchase_date` and `id_user`, accept optional `observation`, validate the date, insert the header, and return `id_purchase`. Details are created in the same request (see purchase-details spec).

#### Scenario: Happy path creation

- GIVEN a valid date "2026-05-17", user id=1, and observation "Compra semanal"
- WHEN the create action is submitted with 3 detail lines
- THEN the purchase header is inserted
- AND all 3 details are inserted linked to the new `id_purchase`
- AND `id_user` matches the authenticated user

#### Scenario: Future date rejected

- GIVEN a purchase_date in the future ("2099-01-01")
- WHEN the create action is submitted
- THEN the system **MUST** reject with "Purchase date cannot be in the future"

#### Scenario: Missing date

- GIVEN an empty purchase_date
- WHEN the create action is submitted
- THEN the system **MUST** reject with "Date is required"

### REQ-PUR-002: List Purchases

The system **MUST** list active purchases (`deleted_at IS NULL`) ordered by `purchase_date` descending, including the user's name via join, with pagination.

#### Scenario: Purchases exist

- GIVEN 3 purchases by user "Admin"
- WHEN the list action is invoked
- THEN all 3 are returned with date, user name, and observation, newest first

#### Scenario: Empty list

- GIVEN no purchases exist
- WHEN the list action is invoked
- THEN an empty result set is returned

### REQ-PUR-003: Read Single Purchase

The system **MUST** return a single purchase by `id_purchase` including all its active purchase-details with product name, supplier name, quantity, and unit price.

#### Scenario: Purchase found with details

- GIVEN a purchase `id_purchase=5` with 2 details
- WHEN reading purchase 5
- THEN the header fields are returned
- AND both details are included with product and supplier names

#### Scenario: Purchase not found

- GIVEN `id_purchase=99` does not exist
- WHEN reading purchase 99
- THEN a 404 error is returned

### REQ-PUR-004: Soft Delete Purchase

The system **MUST** set `deleted_at` on the purchase header. It **SHOULD** also soft-delete all associated purchase-details.

#### Scenario: Happy path delete

- GIVEN an active purchase with 2 details
- WHEN the delete action is invoked
- THEN the purchase header gets `deleted_at` set
- AND both details also get `deleted_at` set

#### Scenario: Already deleted

- GIVEN a purchase already soft-deleted
- WHEN delete is invoked again
- THEN the system returns success (idempotent)

### REQ-PUR-005: Auth Guard

All purchase actions **MUST** require an active Administrator session.

#### Scenario: Unauthenticated

- GIVEN no active session
- WHEN accessing any purchase action
- THEN the system redirects to login

#### Scenario: Non-admin

- GIVEN a session with role different from Administrator
- WHEN accessing any purchase action
- THEN the system returns "Access denied"
