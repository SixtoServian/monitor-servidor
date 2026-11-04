```bash

php artisan key:generate
composer install
npm install
php artisan serve
```
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


-------------------------------------------

TP Logging Deteccion de Incidentes Laravel

'email' => 'test@test.com'
'password' => '123456'

-------------------------------------------

# 🔒 Módulo de Logging y Detección de Incidentes

Este proyecto integra un sistema de registro de eventos de autenticación (basado en los niveles de gravedad del RFC 5424) y un mecanismo de defensa contra ataques de fuerza bruta.

## 🛡️ Detalles de la Implementación
*   **Autenticador utilizado:** El sistema nativo de Laravel (`Illuminate\Support\Facades\Auth` con el método `attempt()`).
*   **Ruta de integración:** La lógica de seguridad (detección y bloqueo) se integró directamente en el controlador `SecurityLoginController` a través de la ruta `POST /security-login`. Se utilizó el middleware `guest` para evitar que usuarios ya logueados accedan a este formulario.
*   **Canal de Logs:** Se creó el canal `security` en `config/logging.php` que guarda los eventos en `storage/logs/security-AAAA-MM-DD.log` con una retención de 30 días.

## 📝 Análisis y Decisiones de Seguridad (Cuestionario)

**1. ¿Qué IP concentra más fallos?**
Durante las pruebas locales y a través del túnel de Cloudflare, la IP `127.0.0.1` (o la IP pública asignada por el túnel) concentró el 100% de los fallos generados intencionalmente para probar el sistema.

**2. ¿Puede afirmarse que existe un ataque?**
No de forma categórica solo con este indicador. Si bien 5 fallos en 5 minutos activan la alerta de posible fuerza bruta, esto también puede deberse a un usuario legítimo que olvidó su contraseña y está intentando varias veces. Para afirmar que es un ataque, se necesitaría correlacionar con otros indicadores (como múltiples IPs atacando la misma cuenta o intentos automatizados masivos).

**3. ¿Qué consecuencias tendría bloquear una IP compartida?**
Un bloqueo a nivel de IP podría dejar fuera de servicio a decenas o cientos de usuarios legítimos. Esto es común en redes corporativas, universidades o redes móviles que usan NAT (Network Address Translation), donde todos los dispositivos salen a internet con la misma IP pública.

**4. ¿Qué otros indicadores usaría el equipo de seguridad?**
*   **Detección por cuenta:** Verificar si múltiples IPs están atacando la misma cuenta de usuario (ataque distribuido).
*   **User-Agent:** Revisar si los intentos fallidos provienen de scripts automatizados (ej. `curl`, `python-requests`) en lugar de navegadores web reales.
*   **Geolocalización:** Verificar si los intentos provienen de países o regiones inusuales para el usuario.
*   **Velocidad de los intentos:** Un humano tarda segundos en escribir, un bot puede hacer miles de peticiones por segundo.

**5. ¿Qué acciones deben ser automáticas y cuáles requieren revisión humana?**
*   **Automáticas:** El bloqueo temporal (10 minutos), la generación de alertas (`ALERT`, `CRITICAL`) y el registro en base de datos y logs. El servidor debe protegerse solo.
*   **Revisión humana:** El bloqueo permanente de una IP, la notificación al usuario afectado, la investigación profunda de si hubo acceso a datos sensibles y la decisión de escalar el incidente a un equipo de respuesta (SIEM/SOC).

## 🚀 Actividad Integradora (Mejoras Propuestas)
Se eligieron dos mejoras para un entorno de producción:
1.  **Detección por cuenta (Rate Limiting por Email):** Además de contar los fallos por IP, implementar un contador por `identifier` (el email). Esto evita que un atacante use una botnet (miles de IPs distintas) para atacar una sola cuenta.
2.  **Notificaciones:** Implementar una notificación por correo electrónico al administrador del sistema cuando se registre un evento `CRITICAL` (IP bloqueada), para que pueda tomar cartas en el asunto en tiempo real sin tener que estar mirando el Dashboard.


-------------

## 📸 Evidencias de Pruebas
*   [Panel Principal](./docs/panel-principal.png)
*   [Monitor del Servisor](./docs/monitor-del-servidor.png)
*   [Prueba de Autenticacion](./docs/prueba-de-autenticacion.png)
*   [Panel de Seguridad](./docs/panel-de-seguridad.png)
*   [Logs del Sistema](./docs/logs-del-sistema.png)
*   [Bloqueo HTTP 429](./docs/bloqueo-1.png)
*   [Bloqueo HTTP 429](./docs/bloqueo-2.png)