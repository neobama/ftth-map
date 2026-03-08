# Setup dengan Nginx Proxy Manager

Konfigurasi ini untuk digunakan dengan **Nginx Proxy Manager** (NPM). NPM akan menangani SSL/HTTPS, jadi aplikasi Laravel hanya perlu listen di HTTP saja.

## 1. Setup Nginx di Server

### Copy file konfigurasi:
```bash
cd /var/www/ftth
sudo cp nginx.conf /etc/nginx/sites-available/ftth
sudo ln -s /etc/nginx/sites-available/ftth /etc/nginx/sites-enabled/
```

### Edit konfigurasi (sesuaikan versi PHP):
```bash
sudo nano /etc/nginx/sites-available/ftth
```

**Cek versi PHP FPM:**
```bash
ls -la /var/run/php/
```

Ubah baris `fastcgi_pass` sesuai versi PHP Anda:
- PHP 8.1: `unix:/var/run/php/php8.1-fpm.sock`
- PHP 8.2: `unix:/var/run/php/php8.2-fpm.sock`
- PHP 8.3: `unix:/var/run/php/php8.3-fpm.sock`

### Set permission:
```bash
sudo chown -R www-data:www-data /var/www/ftth
sudo chmod -R 775 /var/www/ftth/storage
sudo chmod -R 775 /var/www/ftth/bootstrap/cache
```

### Test dan reload:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

## 2. Setup di Nginx Proxy Manager

### Langkah-langkah:

1. **Login ke Nginx Proxy Manager**
   - Biasanya: `http://your-server-ip:81`

2. **Tambah Proxy Host**
   - Klik **"Proxy Hosts"** → **"Add Proxy Host"**

3. **Konfigurasi Details:**
   - **Domain Names**: `ftth.yourdomain.com` (atau domain yang diinginkan)
   - **Scheme**: `http`
   - **Forward Hostname/IP**: `localhost` atau `127.0.0.1`
   - **Forward Port**: `80`
   - **Cache Assets**: ✅ (opsional)
   - **Block Common Exploits**: ✅ (disarankan)
   - **Websockets Support**: ✅ (untuk Livewire)

4. **SSL Tab:**
   - **SSL Certificate**: Pilih certificate atau request baru
   - **Force SSL**: ✅ (disarankan)
   - **HTTP/2 Support**: ✅
   - **HSTS Enabled**: ✅ (opsional)

5. **Advanced Tab (opsional):**
   ```nginx
   # Custom headers jika diperlukan
   add_header X-Frame-Options "SAMEORIGIN" always;
   add_header X-Content-Type-Options "nosniff" always;
   ```

6. **Save** dan test akses

## 3. Konfigurasi Laravel .env

Pastikan `.env` sudah benar:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ftth.yourdomain.com

# Trust proxy untuk mendapatkan IP asli
TRUSTED_PROXIES=*

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ftthdb
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

### Trust Proxy Configuration

Edit `bootstrap/app.php` atau buat middleware untuk trust proxy:

```php
// Di AppServiceProvider atau middleware
public function boot()
{
    // Trust all proxies (atau spesifik IP NPM)
    Request::setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}
```

Atau di Laravel 11+, edit `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
})
```

## 4. Optimize Laravel

```bash
cd /var/www/ftth

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

## 5. Test Akses

1. Akses melalui NPM: `https://ftth.yourdomain.com`
2. Test admin panel: `https://ftth.yourdomain.com/admin`
3. Cek apakah semua asset (CSS, JS) ter-load dengan benar

## Troubleshooting

### IP tidak terdeteksi dengan benar:
- Pastikan `set_real_ip_from` di nginx.conf sudah benar
- Pastikan Laravel trust proxy sudah dikonfigurasi

### Asset tidak ter-load:
- Cek `APP_URL` di `.env` sudah benar
- Clear cache: `php artisan config:clear && php artisan config:cache`
- Pastikan `ASSET_URL` di `.env` jika menggunakan CDN

### 502 Bad Gateway:
- Cek PHP-FPM status: `sudo systemctl status php8.2-fpm`
- Cek nginx error log: `sudo tail -f /var/log/nginx/error.log`
- Pastikan socket path di `fastcgi_pass` benar

### Permission denied:
```bash
sudo chown -R www-data:www-data /var/www/ftth
sudo chmod -R 775 /var/www/ftth/storage
sudo chmod -R 775 /var/www/ftth/bootstrap/cache
```

## Keuntungan Menggunakan NPM

1. ✅ SSL/HTTPS dihandle oleh NPM (Let's Encrypt otomatis)
2. ✅ Multiple domain mudah dikelola
3. ✅ Web interface yang user-friendly
4. ✅ Auto-renewal SSL certificate
5. ✅ Built-in security features
6. ✅ Websocket support untuk Livewire

## Catatan

- Aplikasi Laravel hanya perlu listen di HTTP (port 80)
- SSL/HTTPS dihandle oleh Nginx Proxy Manager
- Pastikan firewall allow port 80 dan 443 (jika NPM di server yang sama)
- Jika NPM di server berbeda, pastikan network connectivity antara NPM dan server Laravel
