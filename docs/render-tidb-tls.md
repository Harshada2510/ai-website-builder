# Render + TiDB Cloud TLS (Laravel)

This project supports TiDB Cloud/MySQL TLS with certificate verification enabled.

## What the app expects

- **CA certificate path (fixed target):** `/etc/ssl/certs/tidb-ca.pem`
- **Laravel env var:** `MYSQL_ATTR_SSL_CA=/etc/ssl/certs/tidb-ca.pem`
- **PDO verification:** `config/database.php` enables PDO MySQL server certificate verification when `MYSQL_ATTR_SSL_CA` is set and the PHP driver supports `PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT` (or `Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT`). Certificate verification is **not** disabled.

## Docker/entrypoint behavior

`docker/entrypoint.sh` runs before Nginx/PHP-FPM start:

1. If `/etc/ssl/certs/tidb-ca.pem` does not exist and a secret file exists at `TIDB_CA_SECRET_FILE` (default: `/etc/secrets/tidb-ca.pem`), it copies it to `/etc/ssl/certs/tidb-ca.pem`.
2. It sets file mode to `0644` (public CA cert only).
3. If `/etc/ssl/certs/tidb-ca.pem` exists and `MYSQL_ATTR_SSL_CA` is empty/unset, it exports `MYSQL_ATTR_SSL_CA=/etc/ssl/certs/tidb-ca.pem`.
4. It does **not** log certificate contents or credentials.

## Configure in Render

1. **Add the public CA certificate as a Render Secret File** (recommended):
   - In Render Dashboard, open your web service.
   - Go to **Environment** / **Secret Files** (Render UI wording may vary).
   - Create a secret file named `tidb-ca.pem`.
   - Paste the **public** TiDB CA certificate contents (for example, the ISRG Root X1/TiDB-provided public CA PEM). Do **not** include private keys or secrets.
   - Render mounts secret files at `/etc/secrets/<filename>`. With the default name, the file will be available at `/etc/secrets/tidb-ca.pem`.

2. **Set environment variables** (Render Dashboard > Environment):
   - `DB_CONNECTION=mysql`
   - `DB_HOST=<your-tidb-host>`
   - `DB_PORT=4000` (or the port provided by TiDB)
   - `DB_DATABASE=ai_website_builder` (or your DB name)
   - `DB_USERNAME=<tidb-user>`
   - `DB_PASSWORD=<tidb-password>` (secret)
   - `MYSQL_ATTR_SSL_CA=/etc/ssl/certs/tidb-ca.pem`
   - `TIDB_CA_SECRET_FILE=/etc/secrets/tidb-ca.pem` (optional if using default)

3. **Redeploy** the service after updating secret files/env vars.

## Security notes

- Use only a **public CA certificate**. Never commit or upload private keys.
- Do not disable certificate verification.
- Do not put CA contents, DB passwords, API keys, or `.env` secrets in logs or git.
- `MYSQL_ATTR_SSL_CA` should point to a readable CA file path inside the container; the entrypoint ensures the fixed target path is prepared before Laravel starts.
