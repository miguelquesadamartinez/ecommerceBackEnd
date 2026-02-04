
@echo off
cd /d C:\inetpub\wwwroot\NoNameEcommerce
:loop
C:\php\php.exe artisan schedule:run
timeout /t 60 /nobreak >nul
goto loop

