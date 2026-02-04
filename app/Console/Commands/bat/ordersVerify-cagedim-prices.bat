@echo off
cd /d "C:\inetpub\wwwroot\NoNameEcommerce"

php artisan orders:verify-cagedim-prices
