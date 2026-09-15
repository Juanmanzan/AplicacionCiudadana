# Aplicación Ciudadana

Sistema de gestión y monitoreo de emergencias ciudadanas desarrollado con **Laravel**, orientado al registro, geolocalización y seguimiento de alertas y reportes de emergencia.

La plataforma permite que los usuarios registren emergencias mediante una API autenticada, administren información asociada a su cuenta y consulten su historial, mientras que el personal administrativo puede visualizar y gestionar emergencias desde un panel de monitoreo.

El sistema incorpora información geográfica mediante **PostgreSQL/PostGIS** y comunicación en tiempo real mediante **Laravel Reverb**.

---

## Características principales

### Gestión de usuarios

El sistema dispone de una API para el registro y autenticación de usuarios.

Entre sus funcionalidades se encuentran:

* Registro de nuevos usuarios.
* Inicio de sesión mediante nombre de usuario o correo electrónico.
* Autenticación mediante tokens.
* Consulta de información del usuario autenticado.
* Actualización del perfil.
* Eliminación de cuenta.
* Cierre de sesión y revocación de tokens.
* Gestión administrativa de usuarios.

La autenticación de la API se realiza mediante **Laravel Sanctum**.

---

## Gestión de emergencias

Los usuarios autenticados pueden generar emergencias utilizando su ubicación geográfica.

El sistema contempla dos modalidades principales:

### Reporte

Permite registrar una emergencia proporcionando:

* Tipo de emergencia.
* Descripción.
* Latitud.
* Longitud.

La emergencia es almacenada inicialmente con el estado:

```text
EN_VERIFICACION
```

### Alerta

Permite generar una emergencia rápidamente utilizando un mensaje previamente configurado por el usuario.

Para enviar una alerta se utiliza:

* Tipo de emergencia.
* Ubicación geográfica.
* Mensaje predefinido asociado al tipo de emergencia.

Esto permite reducir el tiempo necesario para registrar una situación urgente.

---

## Estados de una emergencia

Las emergencias pueden encontrarse en los siguientes estados:

```text
EN_VERIFICACION
CONFIRMADA
ATENDIDA
FALSA_ALARMA
```

Cuando se registra una emergencia, esta inicia en estado:

```text
EN_VERIFICACION
```

Posteriormente puede ser confirmada, atendida o marcada como falsa alarma.

---

## Cancelación de emergencias

Una emergencia recién creada puede cancelarse durante un período limitado.

El sistema establece automáticamente una ventana de cancelación y valida que:

* La emergencia continúe en estado `EN_VERIFICACION`.
* El tiempo permitido para cancelar no haya expirado.
* El usuario tenga autorización para cancelar la emergencia.

Cuando una emergencia es cancelada, pasa al estado:

```text
FALSA_ALARMA
```

---

## Geolocalización

Cada emergencia almacena la ubicación desde la cual fue generada.

Para ello se utilizan coordenadas:

```text
Latitud
Longitud
```

y funciones espaciales de **PostGIS**.

Ejemplo de creación de un punto geográfico:

```sql
ST_SetSRID(
    ST_MakePoint(longitud, latitud),
    4326
)
```

El sistema utiliza el sistema de referencia espacial:

```text
EPSG:4326
```

correspondiente a coordenadas geográficas utilizadas normalmente por GPS.

Esto permite posteriormente recuperar la posición mediante funciones como:

```sql
ST_X()
ST_Y()
```

---

## Comunicación en tiempo real

La aplicación implementa eventos en tiempo real para informar al panel administrativo cuando una emergencia:

```text
es creada
```

o:

```text
es actualizada
```

Los principales eventos del sistema son:

```text
EmergenciaCreada
EmergenciaActualizada
```

Estos eventos se publican mediante el canal privado:

```text
emergencias.admin
```

con los nombres:

```text
emergencia.creada
emergencia.actualizada
```

La comunicación WebSocket puede realizarse mediante **Laravel Reverb**.

---

## Panel de monitoreo

La aplicación incluye una interfaz web protegida para el monitoreo de emergencias.

Entre sus rutas principales se encuentran:

```text
/monitoreo
/historial
```

Desde el sistema administrativo se pueden consultar emergencias activas y modificar su estado.

Las emergencias consideradas activas corresponden principalmente a los estados:

```text
EN_VERIFICACION
CONFIRMADA
```

---

## Historial de emergencias

Cada usuario puede consultar las emergencias que ha generado.

La API permite aplicar filtros por:

* Estado.
* Tipo de emergencia.
* Fecha específica.
* Fecha inicial.
* Fecha final.
* Cantidad máxima de resultados.

Ejemplo:

```http
GET /api/emergencias/mis-emergencias
```

También pueden utilizarse parámetros:

```text
?estado=ATENDIDA
?tipo=1
?fecha=2026-01-15
?desde=2026-01-01&hasta=2026-01-31
?limit=100
```

---

## Contactos de emergencia

Cada usuario puede administrar sus propios contactos de emergencia.

La API permite:

```text
Consultar contactos
Crear contactos
Actualizar contactos
Eliminar contactos
```

Endpoints:

```http
GET    /api/contactos
POST   /api/contactos
PUT    /api/contactos/{id}
DELETE /api/contactos/{id}
```

---

## Mensajes predefinidos

Los usuarios pueden configurar mensajes asociados a diferentes tipos de emergencia.

Estos mensajes pueden utilizarse posteriormente para generar alertas rápidamente.

Endpoints:

```http
GET    /api/mensajes-predefinidos
POST   /api/mensajes-predefinidos
PUT    /api/mensajes-predefinidos/{id}
DELETE /api/mensajes-predefinidos/{id}
```

---

## API REST

La aplicación expone una API para la comunicación con clientes externos, como aplicaciones móviles.

### Autenticación

Registro:

```http
POST /api/register
```

Inicio de sesión:

```http
POST /api/login
```

Obtener usuario autenticado:

```http
GET /api/me
```

Cerrar sesión:

```http
POST /api/logout
```

Las rutas protegidas requieren un token de **Laravel Sanctum**.

Ejemplo:

```http
Authorization: Bearer TOKEN
```

---

## Emergencias

Crear reporte:

```http
POST /api/emergencias/reporte
```

Crear alerta:

```http
POST /api/emergencias/alerta
```

Consultar historial propio:

```http
GET /api/emergencias/mis-emergencias
```

Consultar una emergencia:

```http
GET /api/emergencias/{id}
```

Cancelar una emergencia:

```http
POST /api/emergencias/{id}/cancelar
```

---

## Gestión del usuario

Actualizar perfil:

```http
PUT /api/usuarios/me
```

Eliminar cuenta:

```http
DELETE /api/usuarios/me
```

El sistema también dispone de operaciones administrativas protegidas mediante middleware:

```text
admin.only
```

para gestionar otros usuarios.

---

## Tecnologías utilizadas

### Backend

* PHP 8.2+
* Laravel 12
* Laravel Sanctum
* Laravel Reverb
* Eloquent ORM
* Laravel Broadcasting

### Base de datos y GIS

* PostgreSQL
* PostGIS
* Clickbar Laravel Magellan

### Frontend

* Blade
* JavaScript
* Vite
* Tailwind CSS
* Axios

### Tiempo real

* Laravel Reverb
* Laravel Echo
* Pusher JS / protocolo compatible con Pusher

### Desarrollo y pruebas

* Composer
* NPM
* PHPUnit
* Laravel Pint
* Laravel Pail

---

## Arquitectura general

El proyecto utiliza la estructura convencional de Laravel basada en separación entre modelos, controladores, rutas, vistas y eventos.

```text
AplicacionCiudadana/
│
├── app/
│   ├── Events/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Api/
│   │       └── Auth/
│   └── Models/
│
├── bootstrap/
│
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── public/
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── storage/
│
├── tests/
│
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

---

## Modelo general de funcionamiento

```text
Cliente / Aplicación
        |
        | HTTP + Token Sanctum
        v
Laravel API
        |
        +----------------------+
        |                      |
        v                      v
 Controladores             Autenticación
        |
        v
     Modelos
        |
        v
PostgreSQL + PostGIS
        |
        v
 Emergencias
 georreferenciadas
        |
        v
Laravel Broadcasting
        |
        v
Laravel Reverb
        |
        v
Panel de monitoreo
```

---

## Requisitos

Antes de ejecutar el proyecto es necesario disponer de:

```text
PHP >= 8.2
Composer
Node.js
NPM
PostgreSQL
PostGIS
```

También se recomienda disponer de las extensiones PHP necesarias para utilizar PostgreSQL.

---

## Instalación

Clonar el repositorio:

```bash
git clone https://github.com/Juanmanzan/AplicacionCiudadana.git
```

Ingresar al proyecto:

```bash
cd AplicacionCiudadana
```

Instalar las dependencias de PHP:

```bash
composer install
```

Instalar las dependencias de Node:

```bash
npm install
```

Crear el archivo de variables de entorno:

```bash
cp .env.example .env
```

En Windows:

```cmd
copy .env.example .env
```

Generar la clave de Laravel:

```bash
php artisan key:generate
```

---

## Configuración de PostgreSQL

El proyecto utiliza funcionalidades geoespaciales de PostgreSQL/PostGIS, por lo que se recomienda configurar la conexión en `.env`.

Ejemplo:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=aplicacion_ciudadana
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

Crear la base de datos:

```sql
CREATE DATABASE aplicacion_ciudadana;
```

Posteriormente habilitar PostGIS:

```sql
CREATE EXTENSION IF NOT EXISTS postgis;
```

Puedes verificar la instalación con:

```sql
SELECT PostGIS_Version();
```

---

## Ejecutar migraciones

Una vez configurada la base de datos:

```bash
php artisan migrate
```

Si el proyecto contiene seeders:

```bash
php artisan db:seed
```

También se pueden ejecutar ambas operaciones mediante:

```bash
php artisan migrate --seed
```

---

## Ejecutar el proyecto

Laravel puede iniciarse mediante:

```bash
php artisan serve
```

La aplicación estará disponible normalmente en:

```text
http://127.0.0.1:8000
```

Para iniciar Vite:

```bash
npm run dev
```

---

## Desarrollo

El proyecto dispone de un script de Composer que permite iniciar varios servicios de desarrollo simultáneamente:

```bash
composer run dev
```

Este comando inicia servicios como:

```text
Laravel
Queue listener
Laravel Pail
Vite
```

---

## WebSockets y Laravel Reverb

El proyecto incluye Laravel Reverb para la transmisión de eventos en tiempo real.

Las variables correspondientes pueden configurarse en `.env`.

Ejemplo:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=app-id
REVERB_APP_KEY=app-key
REVERB_APP_SECRET=app-secret

REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

Para iniciar Reverb:

```bash
php artisan reverb:start
```

Una vez activo, las nuevas emergencias y cambios realizados sobre ellas pueden transmitirse al panel de monitoreo mediante WebSockets.

---

## Compilar recursos frontend

Para desarrollo:

```bash
npm run dev
```

Para producción:

```bash
npm run build
```

---

## Pruebas

El proyecto utiliza PHPUnit.

Las pruebas pueden ejecutarse mediante:

```bash
php artisan test
```

o utilizando el script configurado en Composer:

```bash
composer test
```

---

## Seguridad

La aplicación implementa diferentes mecanismos de seguridad proporcionados por Laravel.

Entre ellos:

* Hash de contraseñas.
* Autenticación mediante Laravel Sanctum.
* Tokens individuales para clientes.
* Revocación de tokens al cerrar sesión.
* Middleware de autenticación.
* Middleware administrativo.
* Validación de datos de entrada.
* Canales privados para eventos administrativos.
* Restricciones para la cancelación de emergencias.
* Separación entre operaciones de usuarios y administradores.

Las credenciales y configuraciones sensibles deben almacenarse únicamente en:

```text
.env
```

Este archivo no debe incluirse en el repositorio.

---

## Flujo simplificado de una emergencia

```text
Usuario autenticado
        |
        v
Selecciona tipo de emergencia
        |
        v
Obtiene ubicación GPS
        |
        v
Envía reporte o alerta
        |
        v
Laravel valida información
        |
        v
PostGIS almacena ubicación
        |
        v
Estado: EN_VERIFICACION
        |
        v
Se emite EmergenciaCreada
        |
        v
Laravel Reverb / WebSocket
        |
        v
Panel de monitoreo
        |
        v
Administrador revisa emergencia
        |
        +------> CONFIRMADA
        |
        +------> ATENDIDA
        |
        └------> FALSA_ALARMA
```

---

## Objetivo del proyecto

El objetivo de **Aplicación Ciudadana** es proporcionar una plataforma que facilite la comunicación entre ciudadanos y personal encargado de atender situaciones de emergencia.

La aplicación combina:

```text
Autenticación
+
API REST
+
Geolocalización
+
Gestión de emergencias
+
Comunicación en tiempo real
+
Panel de monitoreo
```

para permitir el registro, localización y seguimiento de incidentes desde una plataforma centralizada.


## Estado del proyecto

Proyecto desarrollado con fines académicos y de aprendizaje, enfocado en la implementación de una arquitectura cliente-servidor, APIs REST, autenticación, manejo de información geoespacial y comunicación en tiempo real con Laravel.


