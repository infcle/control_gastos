# Categories Specification

## Purpose

Manage product classification categories. Each category has a unique name and optional description to organize products for reporting and filtering.

## Requirements

### REQ-CAT-001: Create Category

The system **MUST** accept `name` and `description`, validate them, insert the record with `status=1`, and return the generated `id_category`.

#### Scenario: Happy path creation

- GIVEN a valid name "Lácteos" and description "Productos derivados de la leche"
- WHEN the create action is submitted
- THEN a new category is created with `status=1`
- AND the generated `id_category` is returned

#### Scenario: Duplicate name rejected

- GIVEN a category "Lácteos" already exists
- WHEN creating another category with name "Lácteos"
- THEN the system rejects with "Category name already exists"

### REQ-CAT-002: List Categories

The system **MUST** list active categories (`deleted_at IS NULL`) ordered by name, with pagination.

#### Scenario: Categories exist

- GIVEN 3 active categories: "Bebidas", "Lácteos", "Panadería"
- WHEN the list action is invoked
- THEN all 3 are returned in alphabetical order

#### Scenario: Empty list

- GIVEN no categories exist
- WHEN the list action is invoked
- THEN an empty result set is returned

### REQ-CAT-003: Read Single Category

The system **MUST** return a single category by its `id_category`.

#### Scenario: Category found

- GIVEN a category with `id_category=2` exists
- WHEN reading category 2
- THEN name and description are returned

#### Scenario: Category not found

- GIVEN `id_category=99` does not exist
- WHEN reading category 99
- THEN a 404 error is returned

### REQ-CAT-004: Update Category

The system **MUST** update name and/or description of an existing active category.

#### Scenario: Happy path update

- GIVEN an active category "Lácteos"
- WHEN renaming it to "Lácteos y Derivados"
- THEN the name is updated and products keep their `id_category` reference

#### Scenario: Soft-deleted category

- GIVEN a category with `deleted_at IS NOT NULL`
- WHEN attempting to update it
- THEN the system rejects with "Category not found"

### REQ-CAT-005: Soft Delete Category

The system **MUST** set `deleted_at` on delete. It **MUST** prevent deletion if any active product references the category.

#### Scenario: Happy path delete

- GIVEN an inactive category with no products assigned
- WHEN the delete action is invoked
- THEN `deleted_at` is set to current timestamp

#### Scenario: Category in use

- GIVEN a category referenced by at least one active product
- WHEN attempting to delete it
- THEN the system rejects with "Category is in use by products"

### REQ-CAT-006: Auth Guard

All category actions **MUST** require an active Administrator session.

#### Scenario: Unauthenticated access

- GIVEN no active session
- WHEN accessing any category action
- THEN the system redirects to login

#### Scenario: Non-admin rejected

- GIVEN a non-Administrator session
- WHEN accessing any category action
- THEN the system returns "Access denied"
