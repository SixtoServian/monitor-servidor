```bash

php artisan key:generate
composer install
npm install
php artisan serve
```
http://127.0.0.1:8000/servidor

# Monitor de Servidor con Laravel

Trabajo Práctico para la materia **Programación IV** (UTN Facultad Regional Resistencia).
El proyecto consiste en una aplicación Laravel que lee métricas del sistema operativo, las muestra en una vista web responsiva (con actualización automática) y las expone a través de una API JSON.

## 🖥️ Sistema Operativo Utilizado
El proyecto fue desarrollado y probado principalmente en **Debian 12 (Linux)**.
Sin embargo, el código del servicio `ServerMetrics` fue diseñado para ser **multiplataforma**, detectando automáticamente el sistema operativo mediante `PHP_OS_FAMILY`. Si se ejecuta en Windows, utiliza scripts de PowerShell (WMI); si se ejecuta en Linux, lee directamente los archivos del sistema (`/proc` y `/sys`).

## 🛠️ Tecnologías
*   Laravel 13 (PHP 8.4.27)
*   Tailwind CSS (para el diseño responsivo)
*   PowerShell / WMI (para Windows)
*   Archivos virtuales `/proc` (para Linux)

---

## 📝 Respuestas a las preguntas de la entrega

### 1. ¿Por qué la temperatura suele aparecer como "No disponible" en Windows?
En Windows, la temperatura se obtiene mediante la clase WMI `MSAcpi_ThermalZoneTemperature`. Esta consulta suele devolver "No disponible" o `null` por dos motivos principales:
1.  **Permisos:** Requiere que la terminal o el proceso se ejecute con privilegios de administrador para acceder al hardware.
2.  **Soporte de la placa madre:** Muchos fabricantes de placas base no exponen los sensores de temperatura a través de ACPI (la interfaz que usa Windows), por lo que la consulta falla incluso siendo administrador.
*Si se abre la terminal como administrador, el valor podría aparecer, siempre y cuando el hardware lo soporte.*

### 2. ¿Por qué el script de PowerShell nunca debe incluir datos que vengan del usuario?
Por un problema de seguridad crítico llamado **Inyección de Comandos (Command Injection)**. 
La función `shell_exec()` ejecuta comandos directamente en el sistema operativo subyacente. Si concatenamos datos ingresados por un usuario (por ejemplo, desde un formulario web) dentro de ese comando, un atacante podría ingresar código malicioso (como `; rm -rf /` en Linux o comandos para descargar malware) y el servidor lo ejecutaría con los mismos privilegios que el servidor web. Por eso, el script de PowerShell en este TP es **totalmente fijo y estático**.

### 3. ¿Qué riesgo tiene dejar `/api/metrics` sin autenticación en un servidor real?
Dejarla pública expone información sensible de la infraestructura (versión exacta del SO, uso de CPU/RAM, espacio en disco, etc.), lo cual es un regalo para un atacante que busca vulnerabilidades.
Además, dado que cada petición ejecuta procesos pesados (como abrir PowerShell o leer el sistema de archivos), un atacante podría hacer miles de peticiones por minuto (ataque DDoS o de fuerza bruta) y saturar el servidor, dejándolo fuera de servicio. En producción, debe protegerse con autenticación (ej. `auth:sanctum`) y límites de peticiones (throttling).

### 4. Con polling cada 5 segundos, ¿cuántos pedidos por minuto recibe el servidor si hay 50 personas mirando?
*   En 1 minuto hay 60 segundos.
*   Si se actualiza cada 5 segundos, cada persona hace `60 / 5 = 12` peticiones por minuto.
*   Con 50 personas: `50 * 12 = 600 peticiones por minuto`.
*(El servidor recibiría 600 pedidos por minuto, aunque los datos no hayan cambiado significativamente).*

### 5. Si una app solo necesita la temperatura, ¿qué datos recibe de más al llamar a `/api/metrics`?
Al consumir este endpoint REST, la aplicación recibe el objeto JSON completo con todos los campos, aunque solo use uno. Recibe de más:
*   `hostname`
*   `os`
*   `php`
*   `disk` (total, libre, porcentaje)
*   `memory` (total, disponible, porcentaje)
*   `cpu_pct`
*   `uptime_s`
*   `at` (timestamp)
Este problema se conoce como **Over-fetching** y es exactamente lo que soluciona GraphQL, permitiendo al cliente pedir únicamente el campo `cpu_temp_c`.


-----------------------------------------------------------------------------------------------------------------------------------------------

TP Logging Deteccion de Incidentes Laravel

'email' => 'test@test.com'
'password' => '123456'