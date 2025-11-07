# BROILINK BACKEND - QUICK SETUP SCRIPT
# Jalankan script ini setelah install Laravel 12 di Laragon

echo "=== BroiLink Backend Setup ==="
echo ""

echo "Step 1: Copy semua file..."
echo "Manual: Copy folder broilink-backend ke C:\laragon\www\"
echo ""

echo "Step 2: Install dependencies..."
cd C:\laragon\www\broilink-backend
composer install
composer require laravel/sanctum
composer require fruitcake/laravel-cors
echo ""

echo "Step 3: Setup environment..."
cp .env.example .env
php artisan key:generate
echo "Edit .env dan sesuaikan DB_DATABASE=broilink_db"
echo ""

echo "Step 4: Publish Sanctum..."
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
echo ""

echo "Step 5: Import database..."
echo "Buka PHPMyAdmin dan import broilink_db.sql"
echo ""

echo "Step 6: Clear cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo ""

echo "Step 7: Test server..."
php artisan serve
echo ""

echo "Setup selesai!"
echo "Test API: POST http://localhost:8000/api/login"
echo "Email: admin@broilink.com"
echo "Password: (lihat di database atau set baru)"
