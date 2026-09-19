# Sistema de Prematrícula y Matrícula — CTP Los Chiles

Sistema web para gestionar la prematrícula y matrícula del Colegio Técnico Profesional de Los Chiles. Permite a los docentes registrar estudiantes por período, y al administrador configurar niveles, secciones, talleres, carreras técnicas y períodos, además de revisar y exportar las solicitudes recibidas.

Construido con [Laravel](https://laravel.com) 13, Blade + Tailwind CSS, y MySQL.

## Modalidades soportadas

- **Diurna**: estudiantes de sétimo a undécimo, organizados por sección y taller (grupos A/B).
- **Nocturna**: carreras técnicas nocturnas, con manejo especial para estudiantes mayores de edad (son su propio encargado).
- **Plan Nacional**: educación especial, con campos propios (adecuación, boleta de ubicación, nivel de funcionamiento, técnicas).

## Roles

- **Docente**: matricula estudiantes en las modalidades que tenga asignadas, ve y gestiona únicamente sus propias matrículas (descarga de PDF, reenvío de correo), y puede **ratificar** de un período a otro a los estudiantes que ya matriculó antes, reutilizando sus datos y solo actualizando la sección/especialidad del nuevo año.
- **Administrador**: gestiona docentes, períodos, modalidades, niveles/secciones/talleres/carreras y su capacidad, revisa y edita todas las matrículas, y exporta a Excel por período y modalidad.

El registro público está deshabilitado a propósito: las cuentas de docente y administrador las crea el administrador desde el panel interno (`/admin/docentes`).

## Requisitos

- PHP 8.3+ con las extensiones habituales de Laravel (mbstring, pdo_mysql, fileinfo, gd/imagick para PDF)
- Composer
- Node.js 18+ y npm
- MySQL 8

## Instalación

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Editá `.env` con los datos reales de tu base de datos y del correo SMTP institucional (ver más abajo). Luego:

```bash
php artisan migrate
php artisan db:seed   # crea el usuario admin inicial y las 3 modalidades (Diurna, Nocturna, Plan Nacional)
npm run build          # o `npm run dev` en desarrollo
php artisan serve
```

El seeder de admin crea `admin@ctp.local` con una contraseña temporal — cambiala desde el sistema apenas entres por primera vez. Después necesitás crear al menos un período de matrícula activo (`/admin/periodos`) y configurar niveles/secciones/talleres o carreras (`/admin/configuracion`) antes de que los docentes puedan matricular.

## Variables de entorno relevantes

- `DB_*`: conexión MySQL.
- `MAIL_*`: SMTP para el envío automático del correo de confirmación de cada matrícula (con el PDF de la boleta adjunto). En desarrollo/pruebas usá `MAIL_MAILER=log` para no enviar correos reales.
- `APP_DEBUG`: **debe quedar en `false` en producción** — con `true` expone trazas de error y detalles internos.
- `SESSION_SECURE_COOKIE`: poné `true` si el sitio corre bajo HTTPS (recomendado en producción).

## Pruebas

```bash
php artisan test
```

Cubre autenticación, control de acceso por rol (docente vs. administrador), que un docente no pueda ver ni descargar matrículas de otro (protección contra IDOR), el manejo de cupos bajo concurrencia (bloqueo a nivel de fila para evitar sobrecupo cuando dos docentes matriculan al mismo tiempo en el último cupo disponible), y el flujo de ratificación entre períodos.

## Estructura relevante

- `app/Http/Controllers/PrematriculaController.php` — flujo del docente: crear, listar, descargar PDF, reenviar correo y ratificar matrículas.
- `app/Http/Controllers/Admin/` — panel de administración (dashboard, matrículas, configuración de niveles/secciones/talleres/carreras, períodos, modalidades, docentes).
- `app/Services/BoletaPdfBuilder.php` — genera la boleta en PDF y le adjunta los documentos digitales entregados.
- `app/Exports/` — exportación a Excel de las matrículas por período/modalidad.

## Seguridad

Antes de publicar o desplegar en producción, verificá que:

- `.env` nunca se suba al repositorio (ya está en `.gitignore`).
- `APP_DEBUG=false` y `APP_ENV=production`.
- La contraseña de la cuenta de correo SMTP y la de la base de datos sean credenciales dedicadas, no reutilizadas.
- Corrés `composer audit` periódicamente para detectar dependencias con vulnerabilidades conocidas.
