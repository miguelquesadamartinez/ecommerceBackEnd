# Script de diagnostico SQL Server para Docker
# Ejecutar como Administrador: .\diagnose-sqlserver.ps1

Write-Host "=== Diagnostico SQL Server para Docker ===" -ForegroundColor Cyan
Write-Host ""

# 1. Verificar servicios SQL Server
Write-Host "1. Estado de servicios SQL Server:" -ForegroundColor Yellow
$services = Get-Service -Name "MSSQL*" -ErrorAction SilentlyContinue
if ($services) {
    foreach ($svc in $services) {
        if ($svc.Status -eq "Running") {
            Write-Host "   [OK] $($svc.DisplayName): $($svc.Status)" -ForegroundColor Green
        } else {
            Write-Host "   [X] $($svc.DisplayName): $($svc.Status)" -ForegroundColor Red
        }
    }
} else {
    Write-Host "   [X] No se encontraron servicios SQL Server" -ForegroundColor Red
}
Write-Host ""

# 2. Verificar puerto 1433
Write-Host "2. Verificando puerto 1433:" -ForegroundColor Yellow
$listening = netstat -ano | Select-String ":1433.*LISTENING"
if ($listening) {
    Write-Host "   [OK] SQL Server esta escuchando en el puerto 1433:" -ForegroundColor Green
    foreach ($line in $listening) {
        Write-Host "     $line"
    }
} else {
    Write-Host "   [X] SQL Server NO esta escuchando en el puerto 1433" -ForegroundColor Red
    Write-Host "     Debes habilitar TCP/IP en SQL Server Configuration Manager" -ForegroundColor Yellow
}
Write-Host ""

# 3. IPs de la maquina
Write-Host "3. Direcciones IP de esta maquina:" -ForegroundColor Yellow
$ips = Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -notlike "*Loopback*" }
foreach ($ip in $ips) {
    Write-Host "   - $($ip.IPAddress) ($($ip.InterfaceAlias))" -ForegroundColor Cyan
}
Write-Host ""

# 4. Reglas de firewall para SQL Server
Write-Host "4. Reglas de firewall para SQL Server:" -ForegroundColor Yellow
$firewallRules = Get-NetFirewallRule -DisplayName "*SQL*" -ErrorAction SilentlyContinue | Where-Object { $_.Enabled -eq $true }
if ($firewallRules) {
    Write-Host "   [OK] Reglas de firewall encontradas:" -ForegroundColor Green
    foreach ($rule in $firewallRules) {
        Write-Host "     - $($rule.DisplayName)"
    }
} else {
    Write-Host "   [X] No se encontraron reglas de firewall para SQL Server" -ForegroundColor Red
}
Write-Host ""

# 5. Prueba de conectividad local
Write-Host "5. Probando conectividad local al puerto 1433:" -ForegroundColor Yellow
try {
    $tcpClient = New-Object System.Net.Sockets.TcpClient
    $tcpClient.Connect("127.0.0.1", 1433)
    Write-Host "   [OK] Conexion local exitosa" -ForegroundColor Green
    $tcpClient.Close()
} catch {
    Write-Host "   [X] No se puede conectar localmente: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Recomendaciones
Write-Host "=== ACCIONES RECOMENDADAS ===" -ForegroundColor Cyan
Write-Host ""

if (-not $services -or ($services | Where-Object { $_.Status -ne "Running" })) {
    Write-Host "1. Inicia el servicio SQL Server:" -ForegroundColor Yellow
    Write-Host "   Start-Service 'MSSQL`$SQLEXPRESS'  # O el nombre de tu instancia" -ForegroundColor Gray
    Write-Host ""
}

if (-not $listening) {
    Write-Host "2. Habilita TCP/IP en SQL Server:" -ForegroundColor Yellow
    Write-Host "   - Abre 'SQL Server Configuration Manager'" -ForegroundColor Gray
    Write-Host "   - Ve a 'SQL Server Network Configuration' -> 'Protocols for [INSTANCIA]'" -ForegroundColor Gray
    Write-Host "   - Clic derecho en 'TCP/IP' -> Enable" -ForegroundColor Gray
    Write-Host "   - Clic derecho en 'TCP/IP' -> Properties -> Tab 'IP Addresses'" -ForegroundColor Gray
    Write-Host "   - Seccion 'IPALL': TCP Port = 1433, TCP Dynamic Ports = (vacio)" -ForegroundColor Gray
    Write-Host "   - Reinicia el servicio SQL Server" -ForegroundColor Gray
    Write-Host ""
}

if (-not $firewallRules) {
    Write-Host "3. Crea regla de firewall:" -ForegroundColor Yellow
    Write-Host "   New-NetFirewallRule -DisplayName 'SQL Server' -Direction Inbound -Protocol TCP -LocalPort 1433 -Action Allow" -ForegroundColor Gray
    Write-Host ""
}

Write-Host "4. Configura Docker para usar SQL Server:" -ForegroundColor Yellow
Write-Host "   - Actualiza tu archivo .env con:" -ForegroundColor Gray
$mainIp = ($ips | Select-Object -First 1).IPAddress
if ($mainIp) {
    Write-Host "   DB_HOST=$mainIp" -ForegroundColor Green
} else {
    Write-Host "   DB_HOST=192.168.x.x  # Tu IP de red local" -ForegroundColor Gray
}
Write-Host "   DB_PORT=1433" -ForegroundColor Gray
Write-Host ""
Write-Host "   - Reinicia Docker:" -ForegroundColor Gray
Write-Host "   docker-compose down" -ForegroundColor Gray
Write-Host "   docker-compose up -d" -ForegroundColor Gray
Write-Host ""

Write-Host "5. Habilita autenticacion SQL Server (si usas credenciales SQL):" -ForegroundColor Yellow
Write-Host "   - En SQL Server Management Studio, clic derecho en servidor -> Properties -> Security" -ForegroundColor Gray
Write-Host "   - Selecciona 'SQL Server and Windows Authentication mode'" -ForegroundColor Gray
Write-Host "   - Reinicia el servicio" -ForegroundColor Gray
Write-Host ""
