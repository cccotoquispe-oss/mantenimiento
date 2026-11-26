# Seguridad y manejo de credenciales

- Nunca publiques `config.php`, contraseñas, claves de administrador, copias de bases de datos ni archivos con datos operativos.
- `config.php` está excluido mediante `.gitignore`; verifica siempre la lista de archivos antes de hacer `git push`.
- Si una contraseña o clave ya fue compartida, publicada o incluida en un ZIP enviado a terceros, cámbiala. No basta con borrarla del último commit: el historial de Git puede conservarla.
- Usa HTTPS en el dominio.
- Restringe la lectura de `api.php?action=load` si los datos no deben ser públicos. La versión actual permite lectura sin autenticación.
- Implementa autenticación, autorización por roles, protección CSRF, límites de intentos y auditoría antes de utilizar información sensible o permitir múltiples administradores.
- Realiza copias de seguridad de MySQL antes de cada actualización.
- El archivo `.htaccess` bloquea el acceso directo a la configuración en servidores Apache compatibles. No lo consideres sustituto de mantener secretos fuera del directorio público cuando el hosting lo permita.

- El HTML de este repositorio se ha preparado sin los datos operativos incrustados que venían en el archivo original. No vuelvas a insertar órdenes, avisos, costos, códigos de equipos ni identificadores de personal en un repositorio público.
- El repositorio también elimina los datos que estaban precargados como HTML renderizado (calendario, avisos y listas de personal), no solo los arreglos JavaScript.
