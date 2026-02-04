@echo off
echo Iniciando contenedores Docker...
docker compose up -d --force-recreate
echo Contenedores iniciados.
pause
