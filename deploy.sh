#!/bin/bash
set -e
echo "=== EduFluxa Deploy Script ==="
cd /home/u955496801/domains/edufluxa.com/public_html

echo "[1/5] Pulling latest code..."
git pull origin master

echo "[2/5] Installing composer dependencies (no-dev)..."
/opt/alt/php81/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction

echo "[3/5] Rebuilding config cache..."
/opt/alt/php81/usr/bin/php artisan config:cache

echo "[4/5] Rebuilding route cache..."
/opt/alt/php81/usr/bin/php artisan route:cache

echo "[5/5] Clearing view cache..."
/opt/alt/php81/usr/bin/php artisan view:clear

echo "=== Resetting OPcache ==="
TOKEN=$(grep OPCACHE_RESET_TOKEN .env | cut -d '=' -f2)
curl -s "https://edufluxa.com/system/opcache-reset/${TOKEN}"
echo ""

echo "=== Deploy complete ==="
echo "NOTE: migrations NOT run automatically. If this deploy includes new migrations, run manually:"
echo "  php artisan migrate --force"
echo "(after confirming safe timing per R4)"
