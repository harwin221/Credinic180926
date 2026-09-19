# CrediNica — Sistema de Gestión de Microcréditos

Sistema integral para la administración de microcréditos: clientes, préstamos, cobranza, reportes financieros y control de cartera. Desarrollado en Laravel 10 con Bootstrap 5.

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=flat&logo=bootstrap&logoColor=white)

---

## Tabla de Contenidos

- [Descripción General](#descripción-general)
- [Tecnologías](#tecnologías)
- [Módulos del Sistema](#módulos-del-sistema)
- [Roles y Permisos](#roles-y-permisos)
- [Reportes Disponibles](#reportes-disponibles)
- [Requisitos del Sistema](#requisitos-del-sistema)
- [Instalación](#instalación)
- [Migraciones Pendientes](#migraciones-pendientes)
- [Tareas Programadas](#tareas-programadas)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Archivos Informativos en Raíz](#archivos-informativos-en-raíz)
- [Seguridad](#seguridad)

---

## Descripción General

CrediNica es una plataforma web para instituciones de microfinanzas que permite gestionar todo el ciclo de vida de un crédito: desde el registro del cliente y la solicitud del préstamo, hasta el seguimiento de cobranza, generación de reportes y control de cartera.

El sistema maneja múltiples roles (Administrador, Cobrador/Agente), múltiples monedas (Córdobas y Dólares), y soporta distintas frecuencias de pago con respeto automático de feriados nacionales y días preferidos de cobro pactados con el cliente.

---

## Tecnologías

### Backend
- **Laravel 10.x** con PHP 8.1+
- **MySQL 8.0+** como base de datos
- **Spatie Laravel Permission** — roles y permisos granulares
- **Maatwebsite Excel** — importación y exportación de archivos Excel
- **Barryvdh DomPDF** — generación de reportes en PDF
- **Vinkla Hashids** — encriptación de IDs en URLs
- **Spatie Laravel HTML** — generación de formularios

### Frontend
- **Bootstrap 5.x** — diseño responsive
- **jQuery 3.x** — interactividad
- **Select2** — selectores avanzados con búsqueda
- **SweetAlert2** — alertas y confirmaciones
- **DataTables** — tablas interactivas con paginación
- **Font Awesome 6.x** — iconografía

---

## Módulos del Sistema

### 1. Clientes
- Registro de personas naturales con datos completos (nombre, cédula, teléfonos, dirección, foto)
- Gestión de documentos adjuntos (DPI, RTN, escrituras, etc.)
- Registro de fiadores con sus propios documentos
- Registro de negocios asociados al cliente
- Asignación de clientes a cobradores/gestores
- Importación masiva desde Excel
- Historial crediticio completo

### 2. Préstamos y Desembolsos
- Flujo completo: Solicitud → Aprobación → Desembolso
- Frecuencias de pago soportadas: **Diario, Semanal, Quincenal, Catorcenal, Mensual**
- Generación automática del plan de pagos con respeto de:
  - Feriados nacionales registrados en el sistema
  - Domingos (nunca son día de cobro)
  - Sábados (solo para préstamos no diarios)
  - Día preferido de cobro pactado con el cliente (Semanal y Catorcenal)
- Cálculo exacto de cuotas sin errores de redondeo (la última cuota absorbe el diferencial)
- Edición de préstamos con pagos ya aplicados (recalcula y redistribuye abonos automáticamente)
- Evidencias fotográficas del desembolso
- Représtamos sobre préstamos existentes
- Simulador de préstamos (administrativo y agente)
- Estados: Vigente, Cancelado, Vencido, Clasificado

### 3. Cobranza y Abonos
- Registro de abonos con múltiples formas de pago: Efectivo, Tarjeta, Cheque, Transferencia
- Distribución automática del abono: **Interés → Capital → Mora**
- Cálculo automático de mora por días de atraso (comando programado diario)
- Anulación de abonos con reversión de estados
- Impresión de recibo de pago
- Arqueo de caja por cobrador

### 4. Vista del Agente/Cobrador
Panel exclusivo para cobradores con tres pestañas:
- **Cuotas del Día** — clientes con cuota programada para hoy
- **Clientes en Mora** — clientes con cuotas vencidas sin pagar
- **Préstamos Vencidos** — préstamos cuyo plazo ya venció con saldo pendiente

Desde esta vista el agente puede registrar abonos directamente.

El módulo de agentes también cuenta con:
- **Recaudo** — vista de recaudo diario del agente (`/agente/recaudo`)
- **Plan de Pago** — consulta del plan de cuotas de un préstamo por cliente (`/agente/reportes/planPago`)

### 5. Solicitudes de Préstamo
- Los agentes crean solicitudes que el administrador aprueba o rechaza
- Vista de solicitudes pendientes, aprobadas y rechazadas del día
- Flujo de aprobación con comentarios

### 6. Configuración
- **Feriados**: Registro de feriados nacionales (recurrentes y no recurrentes). Botón para recalcular masivamente todos los planes de pago activos cuando se agregan feriados nuevos.
- **Tipos de Documentos**: Configuración de documentos requeridos
- **Tipos de Negocios**: Catálogo de tipos de negocio
- **Tipo de Cambio**: Registro diario del tipo de cambio Córdoba/Dólar (importación desde Excel)
- **Horarios de Acceso**: Restricción de acceso al sistema por horario y rol
- **Usuarios en Línea**: Monitoreo de sesiones activas

### 7. Reportes
Ver sección [Reportes Disponibles](#reportes-disponibles).

---

## Roles y Permisos

El sistema usa **Spatie Laravel Permission** con tres roles principales:

| Rol | Descripción |
|-----|-------------|
| **Administrador** | Acceso completo al sistema |
| **Cobrador / Agente** | Vista propia de cartera, registro de abonos, creación de solicitudes |
| **Supervisor** | Acceso a reportes y consultas sin modificar datos |

Los permisos son granulares por módulo y acción. Se asignan desde el módulo de Roles y Permisos en la interfaz administrativa.

Middleware de seguridad implementados:
- `adminMiddleware` — restringe acceso a rutas administrativas
- `accessTimeMiddleware` — valida horario de acceso para administradores
- `accessCobradorTimeMiddleware` — valida horario de acceso para cobradores
- `CheckUserActive` — verifica que el usuario esté activo
- `LastUserActivity` — registra última actividad del usuario

---

## Reportes Disponibles

Todos los reportes se abren en una nueva pestaña desde el módulo de Reportes. Cada uno tiene un modal de filtros antes de generar. Soportan impresión directa desde el navegador.

| Reporte | Descripción | Excel | PDF |
|---------|-------------|-------|-----|
| **Desembolsos** | Préstamos desembolsados por período y cobrador | ✅ | ✅ |
| **Recuperación** | Pagos recibidos por período | ✅ | ✅ |
| **Cuotas Vencidas** | Cuotas impagas ordenadas por cobrador y cliente | ✅ | ✅ |
| **Créditos Vencidos** | Préstamos con plazo vencido y saldo pendiente | ✅ | ✅ |
| **Asignación de Clientes** | Clientes asignados por cobrador | ✅ | ✅ |
| **Cobros sin Abono** | Cobros del día sin abono registrado | ✅ | — |
| **Saldo de Cartera** | Resumen de cartera activa con 14 columnas clave | ✅ | ✅ |
| **Clientes Inactivos** | Clientes sin préstamos activos (para reactivación) | — | ✅ |
| **Estado de Cuenta Cliente** | Historial completo de movimientos por préstamo | — | ✅ |
| **Plan de Pago** | Plan de cuotas de un préstamo específico | — | ✅ |
| **Clasificación CONAMI** | Antigüedad de saldos por rangos de días | ✅ | ✅ |
| **Cartera Diaria** | Dividido en 3 secciones: Cuotas del Día, Mora, Vencidos | ✅ | ✅ |
| **Colocación vs Recuperación** | Comparativo entre montos desembolsados y montos recuperados por período | — | ✅ |

---

## Requisitos del Sistema

### Servidor
- PHP >= 8.1 con extensiones: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo, GD
- MySQL >= 8.0 o MariaDB >= 10.3
- Apache 2.4+ o Nginx (con mod_rewrite habilitado)
- Composer 2.x
- Node.js 16+ y NPM

### Configuración PHP recomendada
```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 12M
```

---

## Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/credinica.git
cd credinica
```

### 2. Instalar dependencias
```bash
composer install
npm install
npm run build
```

### 3. Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con los datos de conexión:
```env
APP_NAME=CrediNica
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=credinica_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Crear base de datos e importar
```sql
CREATE DATABASE credinica_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
```bash
mysql -u root -p credinica_db < "base de datos.sql"
```

### 5. Ejecutar migraciones pendientes
```bash
php artisan migrate
```

### 6. Permisos de almacenamiento
```bash
# Linux
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Windows (CMD como Administrador)
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T
```

### 7. Iniciar servidor de desarrollo
```bash
php artisan serve
```

---

## Migraciones Pendientes

Si se está instalando el sistema desde cero usando el archivo `base de datos.sql`, es necesario ejecutar las siguientes migraciones que agregan columnas y optimizaciones que no están en el SQL base:

### Migración 1: Índices de Rendimiento
**Archivo:** `database/migrations/2026_04_26_000001_add_performance_indexes.php`

Agrega índices a las tablas principales para mejorar el rendimiento de los reportes. Reduce tiempos de consulta en reportes con grandes volúmenes de datos.

```bash
php artisan migrate
```

Índices que agrega:
- `prestamos`: índices en `desembolsado+estado`, `agente_id`, `user_id`, `fecha_desembolso`, `forma_pago_tipo`
- `prestamo_coutas`: índices en `prestamo_id+fecha_cuota`, `estado`, `fecha_cuota`
- `prestamo_cuota_abono`: índices en `prestamo_cuota_id+fecha_abono`, `estado`, `fecha_abono`
- `abonos`: índices en `prestamo_id+fecha_abono`, `created_user_id`, `estado`
- `users`: índice en `tipo_usuario`

### Migración 2: Día Preferido de Pago (REQUERIDA)
**Archivo:** `database/migrations/2026_04_30_144642_add_dia_preferido_columns_to_prestamos_table.php`

Agrega dos columnas a la tabla `prestamos` para soportar el día preferido de cobro pactado con el cliente.

```bash
php artisan migrate
```

Columnas que agrega a la tabla `prestamos`:

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `dia_pago_preferido` | `integer nullable` | Día del mes preferido para préstamos quincenales (1–31) |
| `dia_semana_preferido` | `integer nullable` | Día de la semana preferido: 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado |

**Equivalente SQL manual** (si no se puede ejecutar `php artisan migrate`):
```sql
ALTER TABLE `prestamos`
  ADD COLUMN `dia_pago_preferido` INT NULL
    COMMENT 'Día del mes preferido para préstamos quincenales (1-31)'
    AFTER `dias_pago`,
  ADD COLUMN `dia_semana_preferido` INT NULL
    COMMENT 'Día de la semana preferido: 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado'
    AFTER `dia_pago_preferido`;
```

> **Nota:** Estas columnas son `nullable`, por lo que los préstamos existentes no se ven afectados. El sistema es 100% retrocompatible: si el campo es NULL, usa la lógica original de generación de cuotas.

### Migración 3: Nuevas columnas para módulos de Recaudo y Colocación vs Recuperación (REQUERIDA)

Esta migración agrega las columnas necesarias para soportar los nuevos módulos de **Recaudo del Agente** y el reporte de **Colocación vs Recuperación**, incorporados en la versión 2.2.0.

```bash
php artisan migrate
```

> **Importante:** Si se omite esta migración, los módulos de Recaudo y el reporte de Colocación vs Recuperación pueden fallar o mostrar datos incompletos. Ejecutar siempre antes de usar estos módulos por primera vez.

---

## Tareas Programadas

El sistema tiene un comando artisan que debe ejecutarse diariamente para calcular la mora de los préstamos:

```bash
# Ejecutar manualmente
php artisan calcular:mora
```

Configurar en crontab del servidor (Linux):
```bash
crontab -e
# Agregar la siguiente línea (ejecuta cada día a las 00:01)
1 0 * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

El scheduler de Laravel está configurado en `app/Console/Kernel.php`.

---

## Estructura del Proyecto

```
credinica/
├── app/
│   ├── Console/Commands/
│   │   └── calcularMora.php          # Comando diario de cálculo de mora
│   ├── Exports/                       # Clases de exportación a Excel (18 reportes)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── agenteControllers/     # Controladores exclusivos del agente
│   │   │   ├── Auth/                  # Autenticación Laravel UI
│   │   │   ├── abonoController.php
│   │   │   ├── configuracionController.php
│   │   │   ├── fiadorController.php
│   │   │   ├── HomeController.php
│   │   │   ├── permissionController.php
│   │   │   ├── prestamosController.php
│   │   │   ├── reportesController.php
│   │   │   ├── roleController.php
│   │   │   ├── UserController.php
│   │   │   └── userNegocioController.php
│   │   ├── Middleware/                # 8 middlewares personalizados
│   │   └── Requests/                  # Form Requests de validación
│   ├── Imports/                       # Importación de clientes y tipo de cambio
│   ├── Models/                        # 20+ modelos Eloquent
│   ├── Observers/                     # Observer de usuarios
│   └── Helpers/helpers.php            # Funciones globales del sistema
├── config/                            # Configuraciones de Laravel y paquetes
├── database/
│   ├── migrations/                    # 25 migraciones
│   └── seeders/                       # 7 seeders (roles, permisos, datos base)
├── public/assets/                     # CSS, JS, imágenes, vendor
├── resources/views/
│   ├── agentesViews/                  # Vistas exclusivas del agente
│   ├── layouts/                       # Layout principal (app.blade.php)
│   ├── prestamos/                     # Vistas de préstamos
│   ├── reportes/                      # Vistas HTML y PDF de reportes
│   │   ├── modals/                    # Modales de filtros de reportes
│   │   └── _corporate_styles.blade.php # Estilos compartidos de reportes
│   ├── usuarios/                      # Vistas de clientes y agentes
│   ├── permissions/                   # Roles y permisos
│   └── utlisComponents/               # Componentes reutilizables
├── routes/web.php                     # Todas las rutas del sistema
├── base de datos.sql                  # Dump completo de la base de datos
├── composer.json
├── package.json
└── .env.example
```

---

## Archivos Informativos en Raíz

Los siguientes archivos `.md` en la raíz del proyecto son **documentación interna** generada durante el desarrollo. No son parte del código funcional pero contienen información útil sobre cambios importantes:

| Archivo | Contenido |
|---------|-----------|
| `CAMBIOS_REDONDEO_CUOTAS.md` | Documenta la corrección del cálculo de cuotas para evitar errores de redondeo. Detalla los 14 archivos modificados y la lógica implementada. |
| `DOCUMENTACION_EDICION_PRESTAMOS.md` | Explica el flujo de edición de préstamos con pagos ya aplicados: cómo se preservan los abonos y se redistribuyen en el nuevo plan. |
| `IMPLEMENTACION_DIA_PREFERIDO_COMPLETADA.md` | Detalla la implementación del día preferido de cobro para préstamos semanales y catorcenales. Incluye los 10 archivos modificados y casos de prueba. |
| `SOLUCION_SIDEBAR_MOVIL.md` | Documenta la solución al problema del menú hamburguesa en dispositivos móviles (APP_URL y JavaScript fallback). |

---

## Seguridad

- **CSRF**: Todos los formularios incluyen token CSRF automático de Laravel
- **Encriptación de IDs**: Se usa Hashids para nunca exponer IDs reales en URLs
- **SQL Injection**: Uso exclusivo de Eloquent ORM y query builder con bindings
- **XSS**: Escape automático en todas las plantillas Blade con `{{ }}`
- **Autenticación**: Laravel UI con contraseñas hasheadas con bcrypt
- **Autorización**: Middleware + Spatie Permission para control granular
- **Horarios de acceso**: Los cobradores y administradores tienen horarios configurables fuera de los cuales el sistema bloquea el acceso
- **Sesiones**: Control de sesiones activas con registro de última actividad

### Para producción
```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
```

Y ejecutar:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Configuración de Servidor Web

### Apache (Virtual Host)
```apache
<VirtualHost *:80>
    ServerName credinica.local
    DocumentRoot "/ruta/al/proyecto/public"
    <Directory "/ruta/al/proyecto/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx
```nginx
server {
    listen 80;
    server_name credinica.local;
    root /ruta/al/proyecto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

**Versión:** 2.2.0  
**Última actualización:** Julio 2026
