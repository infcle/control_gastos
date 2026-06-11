# Control de Gastos - Sistema PHP

Sistema de control de gastos desarrollado en PHP puro con arquitectura MVC.

## 🚀 Características

- Login seguro con hash de contraseñas (bcrypt)
- Arquitectura MVC (Modelo-Vista-Controlador)
- Base de datos MySQL con migraciones versionadas
- Gestión de roles (Administrator / User) y usuarios con foto de perfil
- CRUD de productos con historial de precios y categorías
- CRUD de categorías, proveedores y compras (con detalle por producto)
- Eliminación lógica (soft delete) en todas las tablas
- Control de acceso por URL mediante helper de autenticación
- Interfaz moderna con Bootstrap 5 e iconos Bootstrap Icons
- Perfil de usuario con foto, nombre y email

## 📋 Requisitos

- PHP 7.4+
- MySQL 5.7+
- Servidor web (Apache/Nginx)
- Extensión mysqli para PHP

## 🛠️ Instalación

1. **Clonar el proyecto**
   ```bash
   git clone <repository-url>
   cd control_gastos
   ```

2. **Configurar base de datos**

   Ejecutar los scripts SQL en orden cronológico (por fecha):
   ```bash
   mysql -u root -p < script_db/29_04_2026_create_tables.sql
   mysql -u root -p < script_db/07_05_2026_create_products_table.sql
   mysql -u root -p < script_db/17_05_2026_create_categories.sql
   mysql -u root -p < script_db/17_05_2026_create_suppliers.sql
   mysql -u root -p < script_db/17_05_2026_fix_products_engine.sql
   mysql -u root -p < script_db/17_05_2026_add_category_to_products.sql
   mysql -u root -p < script_db/17_05_2026_create_purchases.sql
   mysql -u root -p < script_db/17_05_2026_add_profile_picture.sql
   mysql -u root -p < script_db/17_05_2026_remove_teacher_role.sql
   ```

3. **Configurar credenciales**

   Editar `config/database.php` si es necesario:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'root123456');  // Tu contraseña
   define('DB_NAME', 'expense_db');
   ```

4. **Configurar servidor web**
   - Apuntar Apache/Nginx al directorio `control_gastos`
   - Verificar la URL base en `config/app_config.php`

5. **Acceder al sistema**
   - URL: `http://localhost/control_gastos/controller/login/`
   - Usuario: `admin`
   - Contraseña: `password`

## 🗄️ Migraciones de Base de Datos

Los scripts SQL están versionados por fecha con el formato `DD_MM_YYYY_descripcion.sql`.
Deben ejecutarse en orden cronológico:

| Orden | Archivo | Descripción |
|-------|---------|-------------|
| 1 | `29_04_2026_create_tables.sql` | Crea la BD, tablas `roles` y `users`, e inserta datos iniciales |
| 2 | `07_05_2026_create_products_table.sql` | Crea tablas `products` y `prices` |
| 3 | `17_05_2026_create_categories.sql` | Crea tabla `categories` con soft delete |
| 4 | `17_05_2026_create_suppliers.sql` | Crea tabla `suppliers` con soft delete |
| 5 | `17_05_2026_fix_products_engine.sql` | Convierte `products` a InnoDB para FK |
| 6 | `17_05_2026_add_category_to_products.sql` | Agrega `id_category` FK a `products` |
| 7 | `17_05_2026_create_purchases.sql` | Crea tablas `purchases` y `purchase_details` |
| 8 | `17_05_2026_add_profile_picture.sql` | Agrega campo `profile_picture` a `users` |
| 9 | `17_05_2026_remove_teacher_role.sql` | Elimina rol Teacher, solo Admin y User |

## 📁 Estructura del Proyecto

```
control_gastos/
├── config/
│   ├── app_config.php              # Configuración de rutas y URLs
│   ├── auth_helper.php             # Helper de autenticación y control de acceso
│   └── database.php                # Configuración de base de datos
├── controller/
│   ├── home/index.php              # Dashboard
│   ├── login/index.php             # Controlador de login
│   ├── user/index.php              # CRUD de usuarios + perfil propio
│   ├── product/index.php           # CRUD de productos + historial de precios
│   ├── category/index.php          # CRUD de categorías
│   ├── supplier/index.php          # CRUD de proveedores
│   └── purchase/index.php          # CRUD de compras (transaccional)
├── model/
│   ├── login/Login.php             # Modelo de login
│   ├── user/User.php               # Modelo de usuarios
│   ├── product/Product.php         # Modelo de productos
│   ├── category/Category.php       # Modelo de categorías
│   ├── supplier/Supplier.php       # Modelo de proveedores
│   └── purchase/Purchase.php       # Modelo de compras
├── view/
│   ├── template/                   # Layout y partials compartidos
│   │   └── partials/
│   │       ├── aside.php           # Sidebar con menú de navegación
│   │       ├── nav-bar.php         # Navbar superior con perfil de usuario
│   │       ├── breadcrumb.php      # Breadcrumb dinámico
│   │       ├── head.php            # CSS y meta tags
│   │       └── ...                 # Footer, scripts, etc.
│   ├── user/                       # Vistas del módulo de usuarios
│   │   ├── content-list.php        # Lista de usuarios
│   │   ├── content-form.php        # Formulario crear/editar usuario
│   │   ├── content-profile.php     # Perfil propio del usuario
│   │   └── content-change-password.php
│   ├── product/                    # Vistas del módulo de productos
│   │   ├── content-list.php        # Lista de productos
│   │   ├── content-form.php        # Formulario crear/editar producto
│   │   └── content-price.php       # Historial de precios
│   ├── category/                   # Vistas de categorías
│   ├── supplier/                   # Vistas de proveedores
│   ├── purchase/                   # Vistas de compras
│   └── assets/                     # CSS, JS, imágenes, uploads
├── script_db/                      # Migraciones SQL versionadas
├── tests/                          # Tests unitarios (PHP CLI)
│   ├── LoginTest.php
│   ├── UserTest.php
│   ├── ProductTest.php
│   ├── CategoryTest.php
│   ├── SupplierTest.php
│   └── PurchaseTest.php
└── index.php                       # Punto de entrada
```

## 🔐 Seguridad

- Contraseñas almacenadas con hash bcrypt (`password_hash`)
- Valores SQL escapados con `real_escape_string`
- Salidas HTML protegidas con `htmlspecialchars`
- Helper de autenticación centralizado (`config/auth_helper.php`)
- Control de acceso por URL: cada controller valida sesión y rol
- Roles disponibles: **Administrator** (acceso total) y **User** (perfil propio)
- Acciones de perfil propio (Mi Perfil, cambiar contraseña) accesibles sin ser admin

## 👤 Usuarios por Defecto

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | password | Administrator |

## 🧪 Ejecutar Pruebas

Requiere PHP CLI y la base de datos `expense_db` configurada.

```bash
php tests/LoginTest.php
php tests/UserTest.php
php tests/ProductTest.php
php tests/CategoryTest.php
php tests/SupplierTest.php
php tests/PurchaseTest.php
```

## 📝 Licencia

MIT License
