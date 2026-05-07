# Control de Gastos - Sistema PHP

Un sistema de control de gastos desarrollado en PHP puro con arquitectura MVC.

## 🚀 Características

- Sistema de login seguro con hash de contraseñas
- Arquitectura MVC (Modelo-Vista-Controlador)
- Base de datos MySQL
- Gestión de roles y usuarios
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
   - Importar el archivo `script_db/create_tables.sql` en tu MySQL
   - Actualizar las credenciales en `config/database.php` si es necesario

3. **Configurar servidor web**
   - Asegurarse que el servidor apunte al directorio `control_gastos`
   - Configurar URL base en `config/app_config.php` si es diferente

4. **Acceder al sistema**
   - URL: `http://localhost/control_gastos/controller/login/`
   - Usuario por defecto: `admin`
   - Contraseña por defecto: `admin123`

## 📁 Estructura del Proyecto

```
control_gastos/
├── config/
│   ├── app_config.php      # Configuración de rutas y URLs
│   └── database.php        # Configuración de base de datos
├── controller/
│   ├── home/
│   └── login/
│       └── index.php      # Controlador de login
├── model/
│   └── login/
│       └── Login.php      # Modelo de login
├── view/
│   ├── auth/
│   │   └── sign-in.php    # Formulario de login
│   └── assets/           # CSS, JS, imágenes
├── script_db/
│   └── create_tables.sql  # Script de base de datos
└── index.php             # Punto de entrada
```

## 🔐 Seguridad

- Las contraseñas se almacenan con hash bcrypt
- Consultas SQL con escape de caracteres
- Sesiones seguras
- Validación de datos de entrada

## 👤 Usuarios por Defecto

| Usuario | Contraseña | Rol |
|---------|------------|-----|
| admin   | admin123   | Administrator |

## 🚀 Cómo Levantar el Proyecto

### 1. Configurar Base de Datos
```bash
# Importar el script SQL en tu MySQL
mysql -u root -p < script_db/create_tables.sql
```

### 2. Configurar Credenciales
Editar `config/database.php` si es necesario:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root123456');  // Tu contraseña
define('DB_NAME', 'expense_db');
```

### 3. Configurar Servidor Web
- Asegurar que Apache/Nginx apunte al directorio `control_gastos`
- Verificar que `http://localhost/control_gastos/` sea accesible

### 4. Acceder al Sistema
- URL: `http://localhost/control_gastos/controller/login/`
- Usuario: `admin`
- Contraseña: `admin123`

## 🧪 Ejecutar Pruebas Unitarias

### Requisitos para Pruebas
- PHP CLI instalado
- Base de datos `expense_db` configurada

### Ejecutar Tests
```bash
# Desde la raíz del proyecto
php tests/LoginTest.php

# Ver resultados esperados:
# ✅ Valid login test PASSED
# ✅ Invalid password test PASSED  
# ✅ Non-existent user test PASSED
# ✅ Empty username test PASSED
# 🎉 All tests PASSED!
```

### Qué Prueban los Tests
1. **Login válido**: Verifica autenticación con credenciales correctas
2. **Contraseña incorrecta**: Rechaza login con contraseña equivocada
3. **Usuario inexistente**: Rechaza login con usuario que no existe
4. **Campos vacíos**: Valida que no se acepten campos vacíos

## 📝 Licencia

MIT License
