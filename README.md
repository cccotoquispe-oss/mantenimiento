# Planificador de Mantenimiento

Aplicación web para consultar y administrar el planificador de mantenimiento, avisos y actividades. El frontend está desarrollado en HTML, CSS y JavaScript; el backend utiliza PHP y MySQL para sincronizar los datos entre navegadores y dispositivos.

## Arquitectura y publicación

Este repositorio contiene el código fuente y sirve como respaldo/versionado del proyecto.

- **GitHub:** almacenamiento del código y control de versiones.
- **InfinityFree:** alojamiento de la aplicación, ejecución de PHP y conexión con MySQL.
- **MySQL:** almacenamiento compartido de órdenes, operarios, avisos y actividades.

> **Importante:** no publiques esta aplicación mediante GitHub Pages. GitHub Pages solo sirve contenido estático y no ejecuta `api.php` ni conecta este proyecto con MySQL. Mantén el sitio operativo en un hosting compatible con PHP/MySQL, como la instalación actual de InfinityFree.

## Estructura

```text
.
├── index.html             # Interfaz y lógica del planificador
├── api.php                # API PHP para leer y guardar datos
├── config.example.php     # Plantilla sin credenciales reales
├── .htaccess              # Protecciones para Apache/InfinityFree
├── .gitignore             # Excluye configuración privada y archivos locales
├── README.md              # Documentación del proyecto
└── SECURITY.md            # Recomendaciones de seguridad
```

El archivo `config.php` no está incluido deliberadamente. Debe crearse en el servidor a partir de `config.example.php`. Además, las matrices de datos operativos incrustadas en el HTML original y los registros que estaban precargados en el calendario y la tabla de avisos se han vaciado para evitar publicar órdenes, avisos, costos, códigos de equipos o referencias de personal en el repositorio. También se retiraron nombres de personal precargados en listas y filtros; configura nuevamente las listas autorizadas de responsables en tu copia privada de producción si las necesitas. En particular, completa `ACTIVITY_RESPONSIBLES` y las opciones del selector `activityResponsible` en `index.html` antes de habilitar el registro de actividades. En la instalación, la información se carga desde MySQL; si la base de datos está vacía o no responde, el HTML de este repositorio no contiene los datos históricos de respaldo.

## Requisitos del servidor

- Hosting que ejecute PHP y permita conexión PHP-PDO con MySQL.
- Extensión PHP PDO MySQL habilitada.
- Base de datos MySQL creada desde el panel del hosting.
- Dominio configurado con HTTPS.

## Instalación o actualización en InfinityFree

1. Crea o verifica la base de datos MySQL desde el panel de InfinityFree.
2. En el administrador de archivos del hosting, abre la carpeta `htdocs` del dominio.
3. Sube `index.html`, `api.php` y `.htaccess`.
4. Crea `config.php` en `htdocs` tomando como referencia `config.example.php`.
5. Completa en `config.php` el host, nombre de base de datos, usuario, contraseña MySQL y una clave de administrador nueva.
6. Verifica que `config.php` no sea accesible directamente desde el navegador.
7. Abre el dominio con HTTPS y realiza una prueba de lectura y actualización con un Excel de prueba.
8. Comprueba la sincronización desde otro navegador o dispositivo.

**No reemplaces ni elimines la base de datos existente durante una actualización del código.** Antes de actualizar, realiza una copia de seguridad de la base de datos y conserva una copia de los archivos publicados.

## Flujo de trabajo recomendado con GitHub

1. Edita el proyecto en tu computadora.
2. Comprueba los cambios en una copia de pruebas; no pruebes importaciones destructivas con datos de producción.
3. Guarda los cambios en Git y súbelos al repositorio de GitHub.
4. Cuando estén verificados, publica manualmente en InfinityFree los archivos de aplicación modificados (`index.html`, `api.php` y `.htaccess`, según corresponda).
5. Mantén `config.php` únicamente en el servidor; nunca lo subas al repositorio.

GitHub no actualizará InfinityFree automáticamente con esta configuración. La publicación es manual hasta que se configure un mecanismo de despliegue seguro.

## Datos y sincronización

La API utiliza la tabla `planificador_data` y almacena los conjuntos de órdenes, operarios, avisos y actividades en columnas JSON. La tabla se crea automáticamente cuando se accede a la API por primera vez. La acción `load` permite consultar los datos; la acción `save` requiere la clave enviada en la cabecera `X-Admin-Key`.

La lectura del endpoint es pública en la versión actual. Por tanto, cualquier persona que conozca la dirección del sitio puede consultar los datos que devuelve la API. No uses esta configuración para información confidencial sin implementar autenticación y autorización de lectura.

## Limitaciones conocidas

- El guardado actual reemplaza el conjunto de datos recibido; no implementa historial de versiones ni resolución de conflictos entre dos administradores que guarden simultáneamente.
- La clave de administrador se solicita desde el navegador y se conserva en `sessionStorage` durante la sesión de esa pestaña.
- El control de acceso actual protege la escritura, pero no constituye un sistema completo de usuarios y roles.
- El botón de exportación del HTML genera una copia estática; no sustituye el despliegue del backend PHP ni la base de datos. No subas a GitHub copias exportadas desde producción, porque pueden volver a incluir datos operativos.

Consulta `SECURITY.md` antes de publicar el repositorio o actualizar el servidor.
