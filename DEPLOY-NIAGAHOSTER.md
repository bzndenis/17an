# Deploy 17an Dashboard ke Niagahoster

Panduan deploy Laravel 13 ke shared hosting Niagahoster (cPanel).

## Persyaratan

| Item | Minimum |
|------|---------|
| PHP | **8.3** (atur via cPanel → Select PHP Version) |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`, `zip` |
| MySQL | MariaDB/MySQL dari cPanel |
| SSL | Aktifkan Let's Encrypt (gratis di Niagahoster) |

---

## Metode A — Document Root ke `/public` (Recommended)

Cocok untuk **addon domain** atau **subdomain** yang bisa ubah Document Root.

### 1. Siapkan file di komputer (Laragon)

```powershell
cd c:\laragon\www\17an
powershell -ExecutionPolicy Bypass -File deploy\prepare-niagahoster.ps1
```

Atau manual:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 2. Upload project

Upload **seluruh folder** project ke server, contoh:

```
/home/username/17an/
```

**Jangan** taruh di `public_html` langsung. Upload via FTP (FileZilla) atau File Manager cPanel.

File/folder yang **WAJIB** ada:
- `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`
- `artisan`, `composer.json`, `composer.lock`
- `public/build/` (hasil `npm run build`)

File yang **TIDAK** perlu diupload:
- `node_modules/`
- `.git/`
- `.env` (buat baru di server)
- `tests/`

### 3. Buat database MySQL

Di cPanel Niagahoster:

1. **MySQL Databases** → buat database (contoh: `user_17an`)
2. Buat user MySQL + password kuat
3. **Add User To Database** → ALL PRIVILEGES
4. Catat: `DB_HOST` (biasanya `localhost`), nama DB, user, password

### 4. Atur Document Root

cPanel → **Domains** / **Addon Domains** → Edit domain → Document Root:

```
/home/username/17an/public
```

### 5. Buat file `.env` di server

Salin `.env.production.example` menjadi `.env`, isi:

```env
APP_URL=https://domainanda.com
APP_KEY=                    # generate di step 6

DB_DATABASE=user_17an
DB_USERNAME=user_17an
DB_PASSWORD=password_kuat
```

### 6. Jalankan perintah (SSH)

Jika paket hosting punya **SSH** (Business ke atas):

```bash
cd ~/17an
bash deploy/niagahoster/post-deploy.sh
```

Atau manual:

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force          # opsional, data dummy
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

### 7. Permission folder

Pastikan writable:

```
storage/
bootstrap/cache/
```

Via File Manager: permission **775** atau **755** (tergantung server).

---

## Metode B — Tanpa ubah Document Root (public_html)

Jika **tidak bisa** ubah Document Root:

1. Upload Laravel ke `/home/username/17an/` (di luar `public_html`)
2. Copy isi folder `public/` ke `public_html/`
3. Edit `public_html/index.php` — ubah path autoload:

```php
require __DIR__.'/../17an/vendor/autoload.php';
$app = require_once __DIR__.'/../17an/bootstrap/app.php';
```

(Gunakan template `deploy/niagahoster/index.php.root`)

4. `.env` tetap di `/home/username/17an/.env`

---

## Tanpa SSH (hanya cPanel)

Jika **tidak ada SSH**:

1. Generate `APP_KEY` di lokal:
   ```bash
   php artisan key:generate --show
   ```
   Copy ke `.env` server.

2. Export database lokal:
   ```bash
   php artisan migrate:fresh --seed
   mysqldump -u root 17an > deploy/17an-database.sql
   ```
   Import `17an-database.sql` via **phpMyAdmin** cPanel.

3. Buat symlink storage manual:
   - Di `public/storage/` buat shortcut/folder → `../storage/app/public`
   - Atau copy isi `storage/app/public` ke `public/storage`

4. Upload `vendor/` dari lokal (hasil `composer install --no-dev`)

5. Cache config: **tidak wajib** di shared hosting tanpa SSH. Hapus file di `bootstrap/cache/` kecuali `.gitignore` jika error.

---

## Konfigurasi PHP di cPanel

**Select PHP Version** → pilih **8.3**

Aktifkan extension:
- pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo, gd, zip

Optional `.user.ini` di folder `public/`:

```ini
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 256M
max_execution_time = 120
```

---

## Cron Job (opsional)

cPanel → **Cron Jobs** — untuk scheduler Laravel:

```
* * * * * cd /home/username/17an && php artisan schedule:run >> /dev/null 2>&1
```

---

## Login default (setelah seed)

| Email | Password |
|-------|----------|
| admin@17an.test | password |

**Ganti password admin** segera setelah deploy production!

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| 500 Internal Server Error | Cek `storage/logs/laravel.log`, permission `storage/` |
| CSS/JS tidak load | Pastikan `public/build/` terupload, `APP_URL` benar |
| Mix/Vite error | Sudah pakai build production, tidak perlu `npm run dev` |
| Storage foto tidak muncul | `php artisan storage:link` atau symlink manual |
| CSRF / session error | `SESSION_SECURE_COOKIE=true` jika HTTPS, cek `APP_URL` |
| PHP version error | Upgrade ke PHP 8.3 di cPanel |

---

## Checklist deploy

- [ ] PHP 8.3 + extension aktif
- [ ] Database MySQL dibuat & diimport/dimigrate
- [ ] `.env` production (`APP_DEBUG=false`)
- [ ] `APP_KEY` sudah diisi
- [ ] `vendor/` terupload
- [ ] `public/build/` terupload
- [ ] Document Root → `public/`
- [ ] Permission `storage/` & `bootstrap/cache/`
- [ ] SSL/HTTPS aktif
- [ ] Storage link OK
- [ ] Login admin berhasil
