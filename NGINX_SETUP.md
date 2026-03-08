# Panduan Setup Nginx untuk FTTH Map Application

## 1. Copy File Konfigurasi ke Server

### Untuk HTTP saja (development/testing):
```bash
sudo cp nginx.conf /etc/nginx/sites-available/ftth
sudo ln -s /etc/nginx/sites-available/ftth /etc/nginx/sites-enabled/
```

### Untuk HTTPS dengan SSL (production):
```bash
sudo cp nginx-ssl.conf /etc/nginx/sites-available/ftth
sudo ln -s /etc/nginx/sites-available/ftth /etc/nginx/sites-enabled/
```

## 2. Edit Konfigurasi

Edit file konfigurasi sesuai kebutuhan:

```bash
sudo nano /etc/nginx/sites-available/ftth
```

### Yang perlu diubah:
1. **server_name**: Ganti `your-domain.com` dengan domain Anda
2. **root**: Pastikan path `/var/www/ftth/public` sesuai dengan lokasi aplikasi
3. **fastcgi_pass**: Sesuaikan dengan versi PHP yang digunakan:
   - PHP 8.1: `unix:/var/run/php/php8.1-fpm.sock`
   - PHP 8.2: `unix:/var/run/php/php8.2-fpm.sock`
   - PHP 8.3: `unix:/var/run/php/php8.3-fpm.sock`

   Cek versi PHP FPM yang terinstall:
   ```bash
   ls -la /var/run/php/
   ```

4. **SSL Certificate** (jika menggunakan HTTPS):
   - Ganti path certificate sesuai lokasi Let's Encrypt atau certificate Anda

## 3. Setup SSL dengan Let's Encrypt (Opsional untuk Production)

```bash
# Install certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx

# Generate SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (sudah otomatis dengan cron)
sudo certbot renew --dry-run
```

## 4. Set Permission untuk Laravel

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/ftth

# Set permission untuk storage dan cache
sudo chmod -R 775 /var/www/ftth/storage
sudo chmod -R 775 /var/www/ftth/bootstrap/cache
```

## 5. Test dan Reload Nginx

```bash
# Test konfigurasi nginx
sudo nginx -t

# Jika test berhasil, reload nginx
sudo systemctl reload nginx
```

## 6. Konfigurasi PHP-FPM (Opsional)

Edit file PHP-FPM pool jika perlu:

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Pastikan setting berikut:
```ini
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

## 7. Konfigurasi Laravel .env

Pastikan `.env` sudah dikonfigurasi dengan benar:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ftthdb
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

## 8. Optimize Laravel untuk Production

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

## 9. Troubleshooting

### Cek error log:
```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# PHP-FPM error log
sudo tail -f /var/log/php8.2-fpm.log

# Laravel log
tail -f /var/www/ftth/storage/logs/laravel.log
```

### Permission issues:
```bash
sudo chown -R www-data:www-data /var/www/ftth
sudo find /var/www/ftth -type f -exec chmod 644 {} \;
sudo find /var/www/ftth -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/ftth/storage
sudo chmod -R 775 /var/www/ftth/bootstrap/cache
```

### Test PHP-FPM:
```bash
# Test PHP
php -v

# Test PHP-FPM status
sudo systemctl status php8.2-fpm
```

## 10. Firewall (Opsional)

Jika menggunakan UFW:
```bash
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
sudo ufw enable
```

## Catatan Penting

1. **Path**: Pastikan path `/var/www/ftth/public` sesuai dengan lokasi aplikasi Anda
2. **PHP Version**: Sesuaikan versi PHP di `fastcgi_pass` dengan versi yang terinstall
3. **Domain**: Ganti `your-domain.com` dengan domain aktual Anda
4. **SSL**: Untuk production, sangat disarankan menggunakan HTTPS
5. **Permissions**: Pastikan permission storage dan cache sudah benar
