# Suppliers Specification

## Purpose

Manage supplier records (name + location) that identify who provides products. Enables associating purchase details with a specific source.

## Requirements

### REQ-SUP-001: Create Supplier

The system **MUST** accept `name` and `location`, validate them, insert the record with `status=1`, and return the generated `id_supplier`.

#### Scenario: Happy path creation

- GIVEN a valid name "Distribuidora Norte" and location "Av. Siempre Viva 742"
- WHEN the create action is submitted
- THEN a new supplier record is created with `status=1`
- AND the generated `id_supplier` is returned

#### Scenario: Empty name rejected

- GIVEN a form submission with empty name
- WHEN create is submitted
- THEN the system **MUST** reject with "Name is required"

### REQ-SUP-002: List Suppliers

The system **MUST** list active suppliers (`deleted_at IS NULL`) ordered by name, with pagination support.

#### Scenario: Suppliers exist

- GIVEN 5 active suppliers in the database
- WHEN the list action is invoked
- THEN all 5 are returned with their `id_supplier` and `name`

#### Scenario: Empty list

- GIVEN no suppliers exist
- WHEN the list action is invoked
- THEN an empty result set is returned

### REQ-SUP-003: Read Single Supplier

The system **MUST** return a single supplier by its `id_supplier`, including name and location.

#### Scenario: Supplier found

- GIVEN a supplier with `id_supplier=3` exists
- WHEN reading supplier 3
- THEN the record with name and location is returned

#### Scenario: Supplier not found

- GIVEN `id_supplier=99` does not exist
- WHEN reading supplier 99
- THEN a 404 error is returned

### REQ-SUP-004: Update Supplier

The system **MUST** update name and/or location of an existing active supplier.

#### Scenario: Happy path update

- GIVEN an active supplier with `id_supplier=3`
- WHEN name is changed to "Distribuidora Sur"
- THEN the name is updated and location remains unchanged

#### Scenario: Soft-deleted supplier

- GIVEN a supplier with `deleted_at IS NOT NULL`
- WHEN attempting to update it
- THEN the system rejects with "Supplier not found"

### REQ-SUP-005: Soft Delete Supplier

The system **MUST** set `deleted_at` to the current timestamp on delete, and **MUST NOT** physically remove the row.

#### Scenario: Happy path delete

- GIVEN an active supplier with `id_supplier=3`
- WHEN the delete action is invoked
- THEN `deleted_at` is set to current timestamp
- AND the supplier no longer appears in active list

#### Scenario: Re-delete is idempotent

- GIVEN a supplier with `deleted_at` already set
- WHEN delete is invoked again
- THEN the system returns success without error

### REQ-SUP-006: Unique Name

The system **SHOULD** enforce unique supplier names across all records to prevent duplicate entries.

#### Scenario: Duplicate rejected

- GIVEN a supplier named "Distribuidora Norte" already exists
- WHEN creating another supplier with the same name
- THEN the system rejects with "Supplier name already exists"

### REQ-SUP-007: Auth Guard

All supplier actions **MUST** require an active Administrator session.

#### Scenario: Unauthenticated access

- GIVEN no active session
- WHEN accessing any supplier action
- THEN the system redirects to login with auth error

#### Scenario: Non-admin role rejected

- GIVEN an active session with role different from Administrator
- WHEN accessing any supplier action
- THEN the system returns "Access denied"
