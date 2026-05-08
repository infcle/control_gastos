# Control de Gastos - Sistema PHP

Sistema de control de gastos desarrollado en PHP puro con arquitectura MVC.

## 🚀 Características

- Login seguro con hash de contraseñas (bcrypt)
- Arquitectura MVC (Modelo-Vista-Controlador)
- Base de datos MySQL con migraciones versionadas
- Gestión de roles y usuarios
- CRUD de productos con historial de precios
- Eliminación lógica (soft delete)
- Interfaz moderna con Bootstrap 5

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

   Ejecutar los scripts SQL en orden:
   ```bash
   mysql -u root -p < script_db/29_04_2026_create_tables.sql
   mysql -u root -p < script_db/07_05_2026_create_products_table.sql
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
   - Contraseña: `admin123`

## 🗄️ Migraciones de Base de Datos

Los scripts SQL están versionados por fecha con el formato `DD_MM_YYYY_descripcion.sql`.
Deben ejecutarse en orden cronológico:

| Orden | Archivo | Descripción |
|-------|---------|-------------|
| 1 | `29_04_2026_create_tables.sql` | Crea la BD, tablas `roles` y `users`, e inserta datos iniciales |
| 2 | `07_05_2026_create_products_table.sql` | Crea tablas `products` y `prices` |

## 📁 Estructura del Proyecto

```
control_gastos/
├── config/
│   ├── app_config.php          # Configuración de rutas y URLs
│   └── database.php            # Configuración de base de datos
├── controller/
│   ├── home/index.php
│   ├── login/index.php         # Controlador de login
│   ├── user/index.php          # Controlador de usuarios
│   └── product/index.php       # Controlador de productos
├── model/
│   ├── login/Login.php         # Modelo de login
│   ├── user/User.php           # Modelo de usuarios
│   └── product/Product.php     # Modelo de productos
├── view/
│   ├── template/               # Layout y partials compartidos
│   ├── user/                   # Vistas del módulo de usuarios
│   ├── product/                # Vistas del módulo de productos
│   └── assets/                 # CSS, JS, imágenes
├── script_db/
│   ├── 29_04_2026_create_tables.sql           # Migración 1: BD base
│   └── 07_05_2026_create_products_table.sql   # Migración 2: productos
├── tests/
│   ├── LoginTest.php           # Tests del modelo de login
│   ├── UserTest.php            # Tests del modelo de usuarios
│   └── ProductTest.php         # Tests del modelo de productos
└── index.php                   # Punto de entrada
```

## 🔐 Seguridad

- Contraseñas almacenadas con hash bcrypt (`password_hash`)
- Valores SQL escapados con `real_escape_string`
- Salidas HTML protegidas con `htmlspecialchars`
- Sesiones con validación de rol en cada controlador

## 👤 Usuarios por Defecto

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin | admin123 | Administrator |

## 🧪 Ejecutar Pruebas

Requiere PHP CLI y la base de datos `expense_db` configurada.

```bash
php tests/LoginTest.php
php tests/UserTest.php
php tests/ProductTest.php
```

## 📝 Licencia

MIT License
