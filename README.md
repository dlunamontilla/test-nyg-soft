# Prueba técnica para NyG Soft

Esta es una prueba técnica para NyG Soft

Para descargar este proyecto, clone el siguiente repositorio:

```bash
git clone git@github.com:dlunamontilla/test-nyg-soft.git
```

Luego, ingrese a `test-nyg-soft` y proceda a instalar las dependencias:

```bash
composer install
```

Después de finalizar, corra el proyecto en el directorio `public/`

## Variables de entorno

Copie y pegue el siguiente fragmento en el archivo `.env`:

```none
DL_DATABASE_HOST = localhost
DL_DATABASE_PORT = 3306
DL_DATABASE_USER = root
DL_DATABASE_PASSWORD =
DL_DATABASE_NAME = test
DL_DATABASE_CHARSET = utf8
DL_DATABASE_COLLATION = utf8_general_ci
DL_DATABASE_PREFIX = dl_
DL_DATABASE_DRIVE = mysql
```
