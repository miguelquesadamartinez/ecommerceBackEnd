<p align="center"><a href="https://laravel.com" target="_blank"><img src="http://91.134.65.206/asset/img/cmc_logo.png"></a></p>

# Sistema E-Commerce Backend - NoName

![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)
![SQL Server](https://img.shields.io/badge/Database-SQL%20Server-orange.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Sistema backend de comercio electrónico desarrollado desde cero para gestión de pedidos farmacéuticos de NoName, donde teleoperadoras registran y procesan pedidos. Incluye integración completa con sistemas SFTP para importación/exportación de datos, autenticación LDAP, procesamiento de archivos Excel/XML y gestión automatizada de inventario y clientes.

---

## 📋 Tabla de Contenidos

1. [Resumen del Proyecto](#-resumen-del-proyecto)
2. [Información del Desarrollador](#-información-del-desarrollador)
3. [Características Principales](#-características-principales)
4. [Arquitectura y Estructura](#-arquitectura-y-estructura)
5. [Stack Tecnológico](#-stack-tecnológico)
6. [Requisitos del Sistema](#-requisitos-del-sistema)
7. [Instalación](#-instalación)
8. [Configuración](#-configuración)
9. [API Documentation](#-api-documentation)
10. [Procesamiento de Archivos](#-procesamiento-de-archivos)
11. [Tareas Automatizadas (Cron)](#-tareas-automatizadas-cron)
12. [Base de Datos](#-base-de-datos)
13. [Sistema de Notificaciones](#-sistema-de-notificaciones)
14. [Testing](#-testing)
15. [Mantenimiento](#-mantenimiento)
16. [Tareas Pendientes](#-tareas-pendientes)

---

## 🎯 Resumen del Proyecto

**NoName E-commerce Backend** es un sistema robusto de gestión de pedidos farmacéuticos desarrollado específicamente para operadores de call center. El sistema gestiona el ciclo completo de vida de los pedidos, desde su creación hasta la integración con sistemas externos de NoName.

### Funcionalidades Core:

- **Gestión de Pedidos**: Creación, modificación y seguimiento de pedidos por teleoperadoras
- **Sincronización Bidireccional**: Importación/exportación automática de datos vía SFTP
- **Gestión de Inventario**: Control de stock, precios y condiciones comerciales
- **Gestión de Clientes**: Sincronización de datos de farmacias y validación de información
- **Integración PharmaML/Cagedim**: Procesamiento de pedidos en formato estándar farmacéutico
- **Reportes Automatizados**: Generación de reportes diarios, semanales, mensuales y trimestrales
- **Sistema de Auditoría**: Tracking completo de cambios en productos y farmacias

---

## 👨‍💻 Información del Desarrollador

**Proyecto**: Backend E-Commerce NoName  
**Tipo**: Sistema empresarial de gestión de pedidos farmacéuticos  
**Desarrollado por**: Miguel Quesada Martinez  
**Ubicación del Proyecto**: `C:\Users\migue\Documents\___CodeS\ecommerceBackEnd`  
**Entorno**: Producción - Windows Server con IIS  
**Estado**: En producción activa

### Contexto del Desarrollo:

Sistema desarrollado desde cero para CallMedicall, implementando un flujo completo de e-commerce B2B para el sector farmacéutico. El sistema procesa pedidos de múltiples farmacias, gestiona inventarios complejos con umbrales de precios y cantidades, e integra con sistemas legacy de NoName mediante SFTP y formatos propietarios.

---

## ✨ Características Principales

### 🛒 Gestión de Pedidos

- Creación y edición de pedidos en tiempo real
- Validación automática de precios y disponibilidad
- Gestión de pedidos urgentes
- Sistema de pedidos bloqueados con reportes
- Integración con sistema Cagedim (PharmaML)
- Procesamiento de cancelaciones de pedidos

### 📦 Gestión de Productos

- Catálogo completo de productos farmacéuticos
- Control de stock en tiempo real
- Sistema de umbrales de precio (ProductThresholdPrice)
- Histórico de cambios de productos
- Gestión de productos no disponibles
- Control de cantidades máximas por producto
- Auditoría de modificaciones

### 🏥 Gestión de Farmacias

- Base de datos completa de farmacias (clientes)
- Sincronización con sistema SAP de NoName
- Validación de códigos CIP13 y SIRET
- Gestión de condiciones comerciales personalizadas
- Histórico de cambios de datos de farmacia
- Sistema de saneamiento de datos de clientes
- Exportación de nuevos clientes/cambios a NoName

### 🔄 Integración SFTP

- Conexión bidireccional con servidores NoName
- Procesamiento automático de archivos entrantes:
    - Maestro de farmacias (Customer_Master)
    - Catálogo de productos
    - Condiciones comerciales (tradePolicy)
    - Archivos de control de productos/precios
    - Pedidos Cagedim (PharmaML)
    - Pedidos cancelados
    - Productos no disponibles
- Generación automática de archivos salientes:
    - Pedidos enviados a NoName
    - Nuevos clientes o cambios
    - Reportes de actividad (diarios, semanales, mensuales, trimestrales)
    - Auditoría de productos
    - Confirmaciones de pedidos

### 🔐 Seguridad y Autenticación

- Autenticación mediante Laravel Sanctum (API tokens)
- Integración con Active Directory (LDAP)
- Sistema de CAPTCHA tras múltiples intentos fallidos
- Tokens con expiración configurable
- Rate limiting en endpoints críticos
- Autenticación de usuarios de call center

### 📊 Reportes y Auditoría

- Reporte de pedidos bloqueados
- Histórico de pedidos (rolling)
- Confirmaciones semanales de pedidos
- Reportes de actividad mensual y trimestral
- Auditoría de cambios de productos
- Tracking de estado de archivos procesados
- Registro de llamadas API (ApiCallCronJob)

---

## 🏗 Arquitectura y Estructura

### Estructura del Proyecto

```
ecommerceBackEnd/
├── app/
│   ├── Console/Commands/          # Comandos Artisan personalizados
│   │   └── bat/                   # Scripts batch para Windows
│   ├── Exceptions/
│   │   └── Handler.php           # Manejo global de excepciones
│   ├── Helpers/                   # Clases Helper principales
│   │   ├── PfizerHelper.php     # Integración con NoName (SFTP, archivos)
│   │   ├── FileProcessHelper.php # Procesamiento de archivos (2672 líneas)
│   │   ├── GenerateHelper.php   # Generación de Excel y reportes
│   │   ├── ExctractHelper.php   # Extracción de datos
│   │   ├── OrderSaver.php       # Lógica de guardado de pedidos
│   │   ├── XmlHelper.php        # Procesamiento XML
│   │   └── InstallHelper.php    # Configuración inicial
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Order/           # Controladores de pedidos
│   │   │   │   ├── OrderCreateController.php
│   │   │   │   ├── OrderItemController.php
│   │   │   │   └── OrderUpdateController.php
│   │   │   ├── CronController.php       # Endpoints para tareas programadas
│   │   │   ├── DataReturnController.php # Consultas y búsquedas
│   │   │   ├── MiscController.php       # Operaciones diversas
│   │   │   ├── AuthController.php       # Autenticación
│   │   │   └── UserController.php
│   │   └── Middleware/
│   ├── Ldap/
│   │   └── LdapUser.php         # Modelo LDAP
│   ├── Mail/                     # Plantillas de correo
│   │   ├── BlockedOrdersReportMail.php
│   │   ├── CustomerUpdateMail.php
│   │   ├── OrderMail.php
│   │   ├── OrderReportMail.php
│   │   ├── TradePolicyEmail.php
│   │   ├── VerifyPricesMail.php
│   │   └── ExceptionOccured.php
│   ├── Models/                   # Modelos Eloquent
│   │   ├── Order.php            # Pedidos
│   │   ├── OrderDetail.php      # Líneas de pedido
│   │   ├── OrderCagedim.php     # Pedidos Cagedim
│   │   ├── OrderCagedimLine.php
│   │   ├── Product.php          # Productos
│   │   ├── ProductHistoric.php  # Histórico de productos
│   │   ├── ProductThresholdPrice.php  # Umbrales de precio
│   │   ├── ProductUnitsSell.php
│   │   ├── Pharmacy.php         # Farmacias (clientes)
│   │   ├── PharmacyHistoric.php
│   │   ├── Category.php
│   │   ├── FileStatus.php       # Estado de archivos procesados
│   │   ├── ApiCallCronJob.php   # Log de llamadas API
│   │   └── User.php
│   └── Providers/
├── bootstrap/
│   ├── app.php
│   └── providers.php
├── config/                       # Configuración
│   ├── app.php
│   ├── auth.php
│   ├── database.php             # SQL Server
│   ├── filesystems.php          # SFTP configuration
│   ├── ldap.php                 # Active Directory
│   ├── mail.php
│   ├── queue.php
│   └── sanctum.php              # API authentication
├── database/
│   ├── migrations/              # 18+ migraciones
│   │   ├── *_create_users_table.php
│   │   ├── *_create_pharmacy_table.php
│   │   ├── *_create_product_table.php
│   │   ├── *_create_order_table.php
│   │   ├── *_create_product_historics_table.php
│   │   ├── *_create_pharmacy_historics_table.php
│   │   ├── *_create_product_threshold_prices_table.php
│   │   └── *_create_file_statuses_table.php
│   ├── factories/
│   └── seeders/
├── public/
│   ├── index.php               # Entry point
│   └── web.config              # IIS configuration
├── resources/
│   ├── views/                  # Plantillas Blade
│   ├── js/                     # Frontend assets (Vite)
│   └── css/                    # Tailwind CSS
├── routes/
│   ├── web.php                 # 200+ líneas de rutas
│   └── console.php
├── storage/
│   └── app/
│       └── private/
│           └── noName/         # Archivos SFTP
│               ├── in/         # Archivos entrantes
│               ├── out/        # Archivos salientes
│               └── temp/       # Archivos temporales
├── samples/                    # Ejemplos de archivos
│   ├── customer file sent by NoName - pharmacies -.txt
│   ├── Fichier contrôle des prix -.csv
│   └── Fichier contrôle des produits -.csv
├── tests/
│   ├── Feature/
│   └── Unit/
├── composer.json               # PHP dependencies
├── package.json                # Node dependencies
├── vite.config.js              # Build configuration
├── tailwind.config.js
└── artisan                     # CLI tool
```

### Patrones de Diseño Implementados

- **Helper Classes**: Separación de lógica de negocio compleja
- **Repository Pattern**: A través de Eloquent Models
- **Service Layer**: Helpers como capa de servicios
- **Queue Jobs**: Para procesamiento asíncrono
- **Mail Templates**: Sistema de correos estructurado
- **API Resources**: Respuestas JSON estandarizadas

---

## 🔧 Stack Tecnológico

### Backend

- **Framework**: Laravel 12.0
- **PHP**: 8.3
- **Database**: Microsoft SQL Server
- **ORM**: Eloquent
- **Authentication**: Laravel Sanctum (API tokens)
- **LDAP**: LdapRecord Laravel (Active Directory integration)

### Dependencias Principales PHP

```json
{
    "directorytree/ldaprecord-laravel": "^3.3", // LDAP/Active Directory
    "dompdf/dompdf": "^3.1", // Generación PDF
    "guzzlehttp/guzzle": "^7.9", // HTTP client
    "league/flysystem-sftp-v3": "^3.29", // SFTP integration
    "phpoffice/phpspreadsheet": "^4.0", // Excel processing
    "simplesoftwareio/simple-qrcode": "^4.2" // QR codes
}
```

### Herramientas de Desarrollo

- **Laravel Tinker**: REPL para debugging
- **Laravel Pail**: Log viewer
- **PHPUnit**: Testing framework
- **Laravel Pint**: Code style fixer
- **Concurrently**: Múltiples procesos en desarrollo

### Infraestructura

- **Servidor Web**: IIS (Windows Server)
- **Queue System**: Laravel Queue con database driver
- **File Storage**: Local + SFTP remoto
- **Cron Jobs**: Windows Task Scheduler
- **Mail Server**: SMTP configurado

---

## 💻 Requisitos del Sistema

### Requisitos de Software

#### PHP 8.2 o superior con extensiones:

- ✅ `ldap` - Integración Active Directory
- ✅ `curl` - HTTP requests
- ✅ `ftp` - Transferencia de archivos
- ✅ `fileinfo` - Detección de tipos MIME
- ✅ `gd` - Procesamiento de imágenes
- ✅ `mbstring` - Strings multibyte
- ✅ `openssl` - Encriptación
- ✅ `zip` - Compresión de archivos
- ✅ `pdo_sqlsrv_82_nts` - Driver SQL Server
- ✅ `sqlsrv_82_nts` - Driver SQL Server
- ✅ `imagick` - Procesamiento avanzado de imágenes

#### Otros Requisitos

- **Composer** 2.x
- **Node.js** 18.x o superior
- **Microsoft SQL Server** 2019 o superior
- **Servidor Web**: IIS 10+ o Apache 2.4+
- **Memoria**: Mínimo 512MB (recomendado 1GB+)
- **Espacio en Disco**: 500MB mínimo

### Requisitos de Red

- Acceso SFTP a servidores de NoName
- Servidor SMTP para envío de correos
- Servidor LDAP/Active Directory para autenticación
- Puerto 1433 abierto para SQL Server

---

## 📥 Instalación

### 1. Clonar el Repositorio

```bash
git clone <repository-url>
cd ecommerceBackEnd
```

### 2. Instalar Dependencias PHP

```bash
composer install
```

### 3. Instalar Dependencias Node

```bash
npm install
```

### 4. Configurar Entorno

```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 5. Configurar Base de Datos

Editar `.env` con datos de SQL Server:

```env
DB_CONNECTION=sqlsrv
DB_HOST=tu_servidor
DB_PORT=1433
DB_DATABASE=pfizer_ecommerce
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 6. Instalar Laravel Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 7. Configurar LDAP

```bash
php artisan vendor:publish --provider="LdapRecord\Laravel\LdapServiceProvider"
```

Configurar en `.env`:

```env
LDAP_HOSTS=tu_servidor_ldap
LDAP_USERNAME=usuario@dominio.com
LDAP_PASSWORD=contraseña
LDAP_BASE_DN="DC=dominio,DC=com"
```

### 8. Ejecutar Migraciones

```bash
php artisan migrate
```

### 9. Configurar Queue System

```bash
php artisan queue:table
php artisan migrate
```

### 10. Ejecutar Instalación Inicial

```bash
# Visitar en el navegador
http://tu-dominio/install

# O ejecutar manualmente
php artisan db:seed --class=DatabaseSeeder
```

### 11. Generar Token API (Opcional)

```bash
php artisan api:token api.user@callmedicall.com --name="api-token"
```

### 12. Iniciar Servicios de Desarrollo

```bash
# Opción 1: Todos los servicios con Composer
composer run dev

# Opción 2: Servicios individuales
php artisan serve
php artisan queue:work --tries=3
php artisan pail --timeout=0
npm run dev
```

### 13. Conexión SFTP (Producción)

```bash
# Ejemplo de conexión SFTP
sftp -P 2222 -i C:\ruta\al\private_key DEV_PFE@172.18.41.85
```

---

## ⚙️ Configuración

### Variables de Entorno (.env)

#### Aplicación

```env
APP_NAME="NoName Ecommerce"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://tu-dominio.com
APP_URL_API=http://tu-dominio.com/api
```

#### Base de Datos

```env
DB_CONNECTION=sqlsrv
DB_HOST=servidor_sql
DB_PORT=1433
DB_DATABASE=nombre_bd
DB_USERNAME=usuario
DB_PASSWORD=contraseña
DB_CHARSET=SQL_Latin1_General_CP1_CI_AS
DB_COLLATION=French_CI_AS
DB_ENCRYPT=true
DB_TRUST_SERVER_CERTIFICATE=false
```

#### Archivos y SFTP

```env
# Rutas locales o SFTP
IN_FOLDER=/ruta/entrada
OUT_FOLDER=/ruta/salida

# Configuración SFTP NoName
PFIZER_FTP_HOST=servidor_sftp
PFIZER_FTP_USER=usuario
PFIZER_FTP_PASS=contraseña
SFTP_PRIVATE_KEY=/ruta/a/clave/privada
SFTP_ROOT=/

# Configuración SFTP Cagedim
CAGEDIM_FTP_HOST=servidor_cagedim
CAGEDIM_FTP_USER=usuario
CAGEDIM_FTP_PASS=contraseña
```

#### Email

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.servidor.com
MAIL_PORT=587
MAIL_USERNAME=usuario@dominio.com
MAIL_PASSWORD=contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@dominio.com
MAIL_FROM_NAME="${APP_NAME}"

# Destinatarios de notificaciones
EMAIL_FOR_APP_ERROR=errores@dominio.com
EMAIL_FOR_INFO=info@dominio.com
EMAIL_FOR_APP_ORDER=pedidos@dominio.com
EMAIL_FOR_APP_CUSTOMER=clientes@dominio.com
```

#### LDAP / Active Directory

```env
LDAP_LOGGING=true
LDAP_HOSTS=ldap.dominio.com
LDAP_USERNAME=usuario@dominio.com
LDAP_PASSWORD=contraseña
LDAP_PORT=389
LDAP_BASE_DN="DC=dominio,DC=com"
LDAP_TIMEOUT=5
LDAP_SSL=false
LDAP_TLS=false
```

#### Sanctum (API Authentication)

```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,tu-dominio.com
SANCTUM_TOKEN_EXPIRATION=480  # minutos
SESSION_LIFETIME=480
```

#### CORS

```env
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://tu-dominio.com
```

#### Google reCAPTCHA

```env
RECAPTCHA_SITE_KEY=tu_site_key
RECAPTCHA_SECRET_KEY=tu_secret_key
```

#### Configuración de Performance

```env
MAX_EXECUTION_TIME=300  # segundos
MEMORY_LIMIT=1000M
```

---

## 🔌 API Documentation

### Autenticación

#### Obtener Token

```http
POST /sanctum/get-token
Content-Type: application/json

{
  "email": "usuario@dominio.com",
  "password": "contraseña",
  "captcha_token": "token_recaptcha"  // Requerido tras 3 intentos fallidos
}
```

**Respuesta exitosa:**

```json
{
    "token": ["2|abc123xyz..."],
    "user_type": ["Call"]
}
```

**Respuesta con error:**

```json
{
    "wrongLogin": ["The provided credentials are incorrect."],
    "show_captcha": true
}
```

#### Verificar Token

```http
POST /sanctum/verify-token
Authorization: Bearer {token}
```

### Endpoints de Búsqueda

#### Buscar Farmacia

```http
POST /api/search/pharmacy-search
Authorization: Bearer {token}
Content-Type: application/json

{
  "search": "término_búsqueda"
}
```

#### Buscar Producto

```http
POST /api/search/product-search
Authorization: Bearer {token}
Content-Type: application/json

{
  "search": "término_búsqueda"
}
```

### Endpoints de Pedidos

#### Obtener Pedido

```http
POST /api/order/get-order
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 123
}
```

#### Agregar Item al Pedido

```http
POST /api/order/item-add
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 123,
  "product_id": 456,
  "quantity": 10
}
```

#### Eliminar Item del Pedido

```http
POST /api/order/item-remove
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 123,
  "order_detail_id": 789
}
```

#### Guardar Pedido

```http
POST /api/order/save-order
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 123,
  "pharmacy_id": 456,
  "order_urgent": false,
  "customer_po": "REF-123",
  "items": [...]
}
```

### Endpoints de Datos

#### Obtener Todas las Farmacias

```http
POST /api/get/pharmacies
Authorization: Bearer {token}
```

#### Obtener Todos los Productos

```http
POST /api/get/products-all
Authorization: Bearer {token}
```

#### Obtener Categorías

```http
POST /api/get/categories
Authorization: Bearer {token}
```

### Endpoints de Actualización

#### Actualizar Productos

```http
POST /api/product-update
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "file": [archivo_excel]
}
```

#### Importar Stock de Productos

```http
POST /api/product-import-stock
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "file": [archivo_excel]
}
```

#### Actualizar Farmacias

```http
POST /api/pharmacy-update
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "file": [archivo_excel]
}
```

### Endpoints de Procesamiento (Cron)

#### Entrada de Datos

```http
GET /api/in/pharmacies          # Procesar archivo de farmacias
GET /api/in/products            # Procesar archivo de productos
GET /api/in/trade-policy        # Procesar condiciones comerciales
GET /api/in/product-control-file
GET /api/in/price-control-file
GET /api/in/blocked-orders-in
GET /api/in/cagedim-orders      # Procesar pedidos Cagedim
GET /api/in/customer-sanitation
GET /api/in/cancelled-orders
```

#### Salida de Datos

```http
GET /api/out/orders-sent-to-noName           # Exportar pedidos
GET /api/out/new-customer-or-change          # Exportar cambios clientes
GET /api/out/rolling-order-history           # Histórico de pedidos
GET /api/out/weekly-order-confirmations      # Confirmaciones semanales
GET /api/out/monthly-activity-reporting      # Reporte mensual
GET /api/out/quarterly-activity-reporting    # Reporte trimestral
GET /api/out/blocked-orders-out              # Reporte pedidos bloqueados
GET /api/out/product-audit                   # Auditoría de productos
```

### Rate Limiting

- **Login**: 6 intentos por minuto
- **Verify Token**: 60 intentos por minuto
- **Otros endpoints**: Configuración por defecto de Laravel

---

## 📁 Procesamiento de Archivos

### Formatos Soportados

- **Excel**: `.xlsx` (PHPSpreadsheet)
- **Texto**: `.txt` (formato propietario NoName)
- **XML**: Pedidos PharmaML
- **CSV**: Archivos de control

### Flujo de Procesamiento

```
┌─────────────────────┐
│  Servidor SFTP      │
│  NoName             │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  /in/{tipo_archivo} │ ◄── Carpetas de entrada
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  PfizerHelper       │
│  getInFiles()       │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ FileProcessHelper   │ ◄── Procesamiento según tipo
│ process*In()        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Base de Datos      │
│  SQL Server         │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  GenerateHelper     │ ◄── Generación de reportes
│  generate*()        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│ /out/{tipo_archivo} │ ◄── Carpetas de salida
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Servidor SFTP      │
│  NoName             │
└─────────────────────┘
```

### Tipos de Archivos de Entrada

#### 1. Farmacias (Customer_Master)

- **Carpeta**: `in/Customer_Master/`
- **Formato**: Texto delimitado por `~`
- **Procesamiento**: `FileProcessHelper::processPharmaciesIn()`
- **Campos**: CIP13, SAP ID, Razón social, Dirección, SIRET, etc.
- **Ejemplo**: `customer file sent by NoName - pharmacies -.txt`

#### 2. Productos (products)

- **Carpeta**: `in/products/`
- **Formato**: Excel (.xlsx)
- **Procesamiento**: `FileProcessHelper::processProductsIn()`
- **Campos**: Código producto, descripción, precio, stock, categoría

#### 3. Condiciones Comerciales (tradePolicy)

- **Carpeta**: `in/tradePolicy/`
- **Formato**: Excel (.xlsx)
- **Procesamiento**: `FileProcessHelper::processTradePolicyIn()`
- **Contenido**: Descuentos, condiciones especiales por farmacia

#### 4. Control de Productos (productControlFile)

- **Carpeta**: `in/productControlFile/`
- **Formato**: CSV
- **Procesamiento**: `FileProcessHelper::processProductControlFileIn()`
- **Contenido**: Modificaciones de cantidades máximas

#### 5. Control de Precios (priceControlFile)

- **Carpeta**: `in/priceControlFile/`
- **Formato**: CSV
- **Procesamiento**: `FileProcessHelper::processPriceControlFileIn()`
- **Contenido**: Cambios de precios o descuentos

#### 6. Pedidos Cagedim (PharmaML)

- **Carpeta**: `in/PharmaML/`
- **Formato**: XML (PharmaML estándar)
- **Procesamiento**: `FileProcessHelper::processCagedimOrdersIn()`
- **Contenido**: Pedidos de software Cagedim de farmacias

#### 7. Pedidos Cancelados (order_cancellation)

- **Carpeta**: `in/order_cancellation/`
- **Formato**: Excel (.xlsx)
- **Procesamiento**: `FileProcessHelper::processCancelledOrdersIn()`

#### 8. Productos No Disponibles (procesUnavailableProducts)

- **Carpeta**: `in/procesUnavailableProducts/`
- **Formato**: Excel (.xlsx)
- **Procesamiento**: `FileProcessHelper::procesUnavailableProductsIn()`

#### 9. Saneamiento de Clientes (customerSanitation)

- **Carpeta**: `in/customerSanitation/`
- **Formato**: Excel (.xlsx)
- **Procesamiento**: `FileProcessHelper::processCustomerSanitationIn()`
- **Contenido**: Correcciones de datos de farmacias desde NoName

### Tipos de Archivos de Salida

#### 1. Pedidos Enviados a NoName

- **Carpeta**: `out/ordersSentToPfizer/`
- **Formato**: Excel (.xlsx)
- **Generación**: `FileProcessHelper::processOrdersSentToPfizerOut()`
- **Contenido**: Pedidos confirmados para procesamiento

#### 2. Nuevos Clientes o Cambios

- **Carpeta**: `out/newCustomerOrChange/`
- **Formato**: Excel (.xlsx) con archivo adjunto RIB si aplica
- **Generación**: `FileProcessHelper::processNewCustomerOrChangeOut()`
- **Contenido**: Solicitudes de alta/modificación de farmacias

#### 3. Histórico de Pedidos (Rolling)

- **Carpeta**: `out/rollingOrderHistory/`
- **Formato**: Excel (.xlsx)
- **Generación**: `FileProcessHelper::processRollingOrderHistoryOut()`
- **Periodicidad**: Cada 2 semanas

#### 4. Confirmaciones Semanales

- **Carpeta**: `out/weeklyOrderConfirmations/`
- **Formato**: Excel (.xlsx)
- **Generación**: `FileProcessHelper::processWeeklyOrderConfirmationsOut()`

#### 5. Reportes de Actividad Mensual

- **Carpeta**: `out/monthlyActivityReporting/`
- **Formato**: Excel (.xlsx)
- **Generación**: `FileProcessHelper::processMonthlyActivityReportingOut()`

#### 6. Reportes de Actividad Trimestral

- **Carpeta**: `out/quarterlyActivityReporting/`
- **Formato**: Excel (.xlsx)
- **Generación**: `FileProcessHelper::processQuarterlyActivityReportingOut()`

#### 7. Pedidos Bloqueados

- **Carpeta**: `out/blockedOrdersOut/`
- **Formato**: Excel (.xlsx)
- **Generación**: `FileProcessHelper::processBlockedOrdersOut()`
- **Notificación**: Email automático con archivo adjunto

#### 8. Auditoría de Productos

- **Carpeta**: `out/productAudit/`
- **Formato**: Excel (.xlsx)
- **Generación**: `FileProcessHelper::processProductAuditOut()`
- **Contenido**: Cambios en catálogo de productos

### Sistema de Estado de Archivos

Cada archivo procesado registra su estado en la tabla `file_statuses`:

```php
FileStatus {
    file_status_filename    // Nombre del archivo
    file_status_status      // Estado: "Starting In/Out process", "Process ended", "Error"
    file_status_source      // "NoName", "Cagedim", "Manual"
    file_status_process     // "In" o "Out"
    file_status_type        // Tipo de archivo
    created_at              // Fecha de inicio
    updated_at              // Fecha de finalización
}
```

### Gestión de Archivos Procesados

Los archivos una vez procesados se mueven a:

```
in/{tipo}/processed/{archivo}_{timestamp}.ext
```

---

## ⏰ Tareas Automatizadas (Cron)

### Comandos Artisan Personalizados

#### Comandos de Entrada (Importación)

```bash
php artisan in:pharmacies                    # Importar farmacias
php artisan in:products                      # Importar productos
php artisan in:trade-policy                  # Importar condiciones comerciales
php artisan in:product-control-file          # Control de productos
php artisan in:price-control-file            # Control de precios
php artisan in:blocked-orders-in             # Pedidos bloqueados
php artisan in:cagedim-orders                # Pedidos Cagedim
php artisan in:customer-sanitation           # Saneamiento de clientes
php artisan in:cancelled-orders              # Pedidos cancelados
php artisan in:process-unavailable-products  # Productos no disponibles
```

#### Comandos de Salida (Exportación)

```bash
php artisan out:orders-sent-to-noName           # Exportar pedidos
php artisan out:new-customer-or-change          # Nuevos clientes/cambios
php artisan out:rolling-order-history           # Histórico 2 semanas
php artisan out:weekly-order-confirmations      # Confirmaciones semanales
php artisan out:monthly-activity-reporting      # Reporte mensual
php artisan out:quarterly-activity-reporting    # Reporte trimestral
php artisan out:blocked-orders-out              # Pedidos bloqueados
php artisan out:product-audit                   # Auditoría productos
```

#### Comandos de Verificación

```bash
php artisan orders:verify-cagedim-prices              # Verificar precios Cagedim
php artisan orders:verify-cagedim-prices-alternative  # Método alternativo
php artisan orders:verify-regular-prices              # Verificar precios regulares
php artisan orders:verify-regular-prices-alternative  # Método alternativo
```

### Gestión del Scheduler

```bash
# Desarrollo: Ejecutar scheduler en foreground
php artisan schedule:work

# Producción: Ejecutar tareas pendientes (añadir a Task Scheduler Windows)
php artisan schedule:run

# Listar todas las tareas programadas
php artisan schedule:list

# Interrumpir tareas en ejecución
php artisan schedule:interrupt
```

### Queue Workers

```bash
# Iniciar worker (procesa jobs en cola)
php artisan queue:work --tries=3

# Worker con timeout
php artisan queue:work --timeout=300

# Reiniciar workers después de deploy
php artisan queue:restart

# Ver estado de la cola
php artisan queue:failed

# Reintentar jobs fallidos
php artisan queue:retry all
```

### Configuración en Windows Task Scheduler

Para ejecutar tareas programadas en Windows Server, crear tarea con:

**Trigger**: Cada minuto (o según necesidad)
**Action**:

```
Program: C:\php\php.exe
Arguments: C:\ruta\al\proyecto\artisan schedule:run
Start in: C:\ruta\al\proyecto
```

### Scripts Batch Incluidos

El proyecto incluye scripts `.bat` en `app\Console\Commands\bat\` para facilitar ejecución en Windows:

```batch
@echo off
cd /d "C:\inetpub\wwwroot\PfizerEcommerce"
php artisan in:pharmacies
```

---

## 🗄️ Base de Datos

### Gestor

**Microsoft SQL Server** 2019+

### Configuración

- **Collation**: `French_CI_AS`
- **Charset**: `SQL_Latin1_General_CP1_CI_AS`
- **Port**: 1433

### Tablas Principales

#### users

Usuarios del sistema (teleoperadoras, administradores)

```sql
- id
- name
- email
- password
- user_type (Call, Admin)
- remember_token
- created_at, updated_at
```

#### pharmacies

Farmacias (clientes)

```sql
- id
- pharmacy_sap_id          # ID en sistema SAP de NoName
- pharmacy_cip13           # Código único farmacia
- pharmacy_name            # Razón social
- pharmacy_name4           # Nombre comercial
- pharmacy_address_*       # Dirección completa
- pharmacy_city
- pharmacy_zipcode
- pharmacy_phone
- pharmacy_siret
- pharmacy_iban
- pharmacy_status          # 0=Active, 1=Blocked, 2=Deleted
- pharmacy_sent_to_pfizer  # 0=Pendiente, 1=Enviado
- pharmacy_lcr             # Pago por LCR
- order_reference          # Referencia de pedido asociado
- created_at, updated_at
```

#### pharmacy_historics

Histórico de cambios en farmacias

```sql
- id
- pharmacy_historic_pharmacy_id
- pharmacy_historic_field          # Campo modificado
- pharmacy_historic_old_value      # Valor anterior
- pharmacy_historic_new_value      # Valor nuevo
- pharmacy_historic_sent_to_pfizer # Enviado a NoName
- created_at, updated_at
```

#### products

Catálogo de productos

```sql
- id
- product_code
- product_name
- product_description
- product_price
- product_stock
- product_available        # Disponibilidad
- product_max_quantity     # Cantidad máxima por pedido
- category_id
- created_at, updated_at
```

#### product_historics

Histórico de cambios en productos

```sql
- id
- product_id
- product_historic_field
- product_historic_old_value
- product_historic_new_value
- product_historic_sent_to_pfizer
- created_at, updated_at
```

#### product_threshold_prices

Umbrales de precio por cantidad

```sql
- id
- product_id
- pharmacy_id              # NULL para todos
- threshold_quantity       # Cantidad mínima
- threshold_price          # Precio aplicable
- created_at, updated_at
```

#### product_units_sells

Unidades vendidas por producto

```sql
- id
- product_id
- pharmacy_id
- units_sold
- period                   # Periodo de venta
- created_at, updated_at
```

#### categories

Categorías de productos

```sql
- id
- category_name
- category_description
- created_at, updated_at
```

#### orders

Pedidos

```sql
- id
- pharmacy_id
- user_id                  # Teleoperadora
- order_status             # 0=Draft, 1=Sent, 2=Blocked, 3=Cancelled
- order_total
- order_urgent             # Pedido urgente
- customer_po              # Referencia cliente
- order_sent_to_pfizer_date
- order_blocked_reason
- created_at, updated_at
```

#### order_details

Líneas de pedido

```sql
- id
- order_id
- product_id
- quantity
- unit_price
- line_total
- created_at, updated_at
```

#### orders_cagedim

Pedidos de sistema Cagedim

```sql
- id
- pharmacy_cip13
- order_date
- order_reference
- order_total
- order_status
- order_xml_content        # XML PharmaML original
- processed                # Procesado
- created_at, updated_at
```

#### orders_cagedim_header_texts

Textos de cabecera Cagedim

#### orders_cagedim_lines

Líneas de pedido Cagedim

```sql
- id
- order_cagedim_id
- product_code
- quantity
- unit_price
- line_total
- created_at, updated_at
```

#### orders_cagedim_line_texts

Textos de línea Cagedim

#### file_statuses

Estado de archivos procesados

```sql
- id
- file_status_filename
- file_status_status       # "Starting In/Out process", "Process ended"
- file_status_source       # "NoName", "Cagedim", "Manual"
- file_status_process      # "In", "Out"
- file_status_type         # Tipo de archivo
- created_at, updated_at
```

#### api_call_cron_jobs

Log de llamadas API/Cron

```sql
- id
- route                    # Endpoint llamado
- response_code            # HTTP status
- response_body            # Respuesta
- execution_time           # Tiempo de ejecución (ms)
- created_at
```

#### personal_access_tokens

Tokens de API (Sanctum)

```sql
- id
- tokenable_type
- tokenable_id
- name
- token                    # Hash del token
- abilities                # Permisos
- expires_at
- created_at, updated_at
```

### Relaciones Principales

```
users (1) ──────▶ (N) orders
pharmacies (1) ──▶ (N) orders
pharmacies (1) ──▶ (N) pharmacy_historics
products (1) ─────▶ (N) order_details
products (1) ─────▶ (N) product_historics
products (1) ─────▶ (N) product_threshold_prices
categories (1) ───▶ (N) products
orders (1) ───────▶ (N) order_details
```

---

## 📧 Sistema de Notificaciones

### Plantillas de Email Implementadas

#### 1. BlockedOrdersReportMail

- **Uso**: Reporte diario de pedidos bloqueados
- **Destinatario**: `EMAIL_FOR_APP_ORDER`
- **Adjuntos**: Excel con listado de pedidos
- **Contenido**: Pedidos bloqueados con razón de bloqueo

#### 2. CustomerUpdateMail

- **Uso**: Notificación de cambios en datos de farmacia
- **Destinatario**: `EMAIL_FOR_APP_CUSTOMER`
- **Adjuntos**: Excel con cambios + RIB si aplica
- **Asunto**: "NoName SAS DEMANDE DE CREATION CODE CIP: {cip13}"

#### 3. OrderMail

- **Uso**: Confirmación de pedido
- **Destinatario**: Farmacia
- **Contenido**: Detalle del pedido con QR code

#### 4. OrderReportMail

- **Uso**: Reportes periódicos de pedidos
- **Destinatario**: `EMAIL_FOR_APP_ORDER`
- **Adjuntos**: Excel con estadísticas

#### 5. ExceptionOccured

- **Uso**: Notificación de errores del backend
- **Destinatario**: `EMAIL_FOR_APP_ERROR`
- **Contenido**: Stack trace y contexto del error

#### 6. ExceptionOccuredFront

- **Uso**: Errores reportados desde el frontend
- **Destinatario**: `EMAIL_FOR_APP_ERROR`
- **Endpoint**: `POST /api/error-notification`

#### 7. InfoMail

- **Uso**: Notificaciones informativas generales
- **Destinatario**: `EMAIL_FOR_INFO`

#### 8. MissingProductsMail

- **Uso**: Productos no encontrados en base de datos
- **Destinatario**: `EMAIL_FOR_INFO`
- **Contenido**: Lista de códigos de producto faltantes

#### 9. MissingPharmaciesMail

- **Uso**: Farmacias no encontradas en base de datos
- **Destinatario**: `EMAIL_FOR_INFO`
- **Contenido**: Lista de CIP13 no encontrados

#### 10. TemporaryPasswordMail

- **Uso**: Envío de contraseña temporal a nuevos usuarios
- **Destinatario**: Usuario creado
- **Contenido**: Credenciales de acceso

#### 11. TradePolicyEmail

- **Uso**: Notificación de actualización de condiciones comerciales
- **Destinatario**: `EMAIL_FOR_INFO`
- **Contenido**: Resumen de cambios en política comercial

#### 12. VerifyPricesMail

- **Uso**: Verificación de precios en pedidos
- **Destinatario**: `EMAIL_FOR_INFO`
- **Contenido**: Discrepancias de precios detectadas

---

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test tests/Feature/OrdersTest.php

# Con coverage
php artisan test --coverage

# Parallel execution
php artisan test --parallel
```

### Estructura de Tests

```
tests/
├── Feature/              # Tests de integración
│   └── OrdersTest.php   # Tests de pedidos
├── Unit/                # Tests unitarios
└── TestCase.php         # Clase base
```

---

## 🔧 Mantenimiento

### Tareas de Mantenimiento Regular

#### Diario

- ✅ Monitorear cola de jobs: `php artisan queue:failed`
- ✅ Revisar logs: `storage/logs/laravel.log`
- ✅ Verificar espacio en disco (carpetas temp)
- ✅ Revisar emails de error

#### Semanal

- ✅ Limpiar archivos temporales antiguos
- ✅ Revisar pedidos bloqueados
- ✅ Verificar sincronización SFTP
- ✅ Revisar históricos de productos/farmacias

#### Mensual

- ✅ Backup completo de base de datos
- ✅ Limpiar tabla `api_call_cron_jobs` (registros antiguos)
- ✅ Limpiar tabla `file_statuses` (registros antiguos)
- ✅ Revisar y actualizar tokens API
- ✅ Revisar logs de rendimiento

#### Trimestral

- ✅ Actualizar dependencias: `composer update`
- ✅ Revisar y optimizar consultas lentas
- ✅ Auditoría de seguridad
- ✅ Revisar configuración de servidor

### Comandos de Limpieza

```bash
# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar jobs fallidos
php artisan queue:flush

# Limpiar sesiones expiradas
php artisan session:gc
```

---

## 📝 Tareas Pendientes

### Prioridad Alta

- [ ] Enviar email cuando un pedido sea desbloqueado
- [ ] Regenerar API Token periódicamente (implementar rotación)
- [ ] Implementar limpieza automática de archivos temp (Cron)
- [ ] Mover archivos a carpeta de error cuando falle el procesamiento

### Prioridad Media

- [ ] Implementar verificación de envío de emails
- [ ] Habilitar middlewares LDAP en `bootstrap/app.php`
- [ ] Migrar funciones PHP a librería XML estándar
- [ ] Implementar tests para todos los Helpers

### Mejoras Futuras

- [ ] Dashboard de monitoreo en tiempo real
- [ ] API de webhooks para notificaciones
- [ ] Sistema de cache Redis
- [ ] Logs estructurados (ELK Stack)
- [ ] Documentación API con Swagger/OpenAPI
- [ ] Implementar CI/CD pipeline
- [ ] Containerización con Docker

### Optimizaciones

- [ ] Optimizar consultas N+1
- [ ] Implementar eager loading en relaciones
- [ ] Índices adicionales en base de datos
- [ ] Cachear respuestas de API frecuentes
- [ ] Optimizar procesamiento de archivos grandes

---

## 📄 Licencia

MIT License

---

## 📞 Soporte

Para problemas o consultas sobre el sistema:

- **Email Errores**: ${EMAIL_FOR_APP_ERROR}
- **Email Info**: ${EMAIL_FOR_INFO}
- **Email Pedidos**: ${EMAIL_FOR_APP_ORDER}
- **Email Clientes**: ${EMAIL_FOR_APP_CUSTOMER}

---

Framework: [Laravel](https://laravel.com)  
Desarrollador: Miguel Quesada Martinez

---

**Versión del README**: 2.0  
**Última actualización**: Enero 2026  
**Versión del Sistema**: Producción
