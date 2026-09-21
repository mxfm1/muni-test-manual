# Manual Muni

Demo PHP de un manual para una aplicación de gestión con arquitectura MVC. Los módulos, tópicos, secciones y media se persisten en MySQL; los binarios de imágenes se almacenan localmente.

## Requisitos

- PHP 8.2 con extensiones `pdo_mysql` y `gd`.
- Composer 2.
- Una base de datos MySQL. TLS es opcional; si se usa, se debe proveer su certificado CA.

## Instalación

1. Ejecutar `composer install`.
2. Copiar `.env.example` a `.env` y completar los valores de MySQL. Definir `DB_SSL_CA` sólo si MySQL requiere TLS.
3. Ejecutar `php database/migrate.php` para aplicar las migraciones pendientes en orden.
4. Usar `public/` como document root del servidor web.

Para desarrollo local, se puede ejecutar `php -S localhost:8000 -t public`.

## Estructura MVC

- `app/Controllers`: recibe solicitudes HTTP, valida sus datos y elige la respuesta.
- `app/Models`: representa y persiste los datos de la aplicación.
- `app/Views`: plantillas PHP que renderizan el HTML.
- `app/Services`: tareas reutilizables, como el almacenamiento de imágenes.
- `app/Support`: configuración, conexión a base de datos y CSRF de sesión.

No hay autenticación todavía. El token CSRF es local a la sesión y protege las mutaciones realizadas desde el formulario.

## Migraciones

Las migraciones se aplican una única vez y quedan registradas en la tabla técnica `schema_migrations`:

```text
001_create_users.sql
002_create_modules.sql
003_create_topics.sql
004_create_sections.sql
005_create_media.sql
006_create_topic_references.sql
```

Cada archivo crea una sola tabla. Las claves foráneas y el orden de versiones expresan las dependencias entre módulos, tópicos, secciones, media y referencias.

## Búsqueda global

MySQL usa índices `FULLTEXT` de InnoDB sobre módulos, tópicos, secciones y media. `HandbookSearchRepository` consulta esos índices en modo de lenguaje natural y devuelve resultados ordenados por relevancia. No usa `LIKE` ni concatena la consulta del usuario en SQL.

Los términos de menos de tres caracteres pueden no indexarse con la configuración predeterminada de MySQL (`innodb_ft_min_token_size`). Si el producto requiere búsquedas de términos muy cortos, esa configuración debe cambiarse y los índices FULLTEXT deben reconstruirse.

## Media

`LocalImageStorage` acepta JPEG, PNG y WebP, limita el tamaño a 5 MB, normaliza a WebP y reduce imágenes a un máximo de 1920 px por lado. Los archivos se escriben en `public/uploads/media/`; Git los ignora. La base almacena metadatos, una clave de almacenamiento y una URL pública local.

La ruta `POST /sections/{sectionId}/media` exige un token CSRF y una sección existente. El contrato `ImageStorage` desacopla el controller del destino: una futura implementación para S3 sólo debe cumplir ese contrato y sustituirse en `public/index.php`.

El contenido HTML de `sections.content` debe sanitizarse antes de persistirse o renderizarse para evitar XSS.
