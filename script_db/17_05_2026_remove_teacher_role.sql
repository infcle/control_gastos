-- Remove Teacher role, keep only Administrator (1) and User (2)
UPDATE users SET id_rol = 2 WHERE id_rol = 3;
DELETE FROM roles WHERE id_rol = 3;
