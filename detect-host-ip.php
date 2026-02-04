<?php
/**
 * Script para detectar la IP del host desde el contenedor Docker
 * Ejecutar: docker-compose exec app php detect-host-ip.php
 */

echo "=== Detectando IP del Host Windows ===\n\n";

// Método 1: Resolver host.docker.internal
echo "1. Intentando resolver host.docker.internal:\n";
$host = gethostbyname('host.docker.internal');
if ($host !== 'host.docker.internal') {
    echo "   ✓ host.docker.internal resuelve a: $host\n";
    $hostIp = $host;
} else {
    echo "   ✗ No se pudo resolver host.docker.internal\n";
    $hostIp = null;
}
echo "\n";

// Método 2: Obtener el gateway del contenedor
echo "2. Detectando gateway de red del contenedor:\n";
$gateway = null;
if (file_exists('/proc/net/route')) {
    $routes = file('/proc/net/route');
    foreach ($routes as $line) {
        $parts = preg_split('/\s+/', trim($line));
        if (count($parts) >= 3 && $parts[1] === '00000000') {
            $hex = $parts[2];
            $gateway = long2ip(hexdec($hex));
            echo "   ✓ Gateway encontrado: $gateway\n";
            break;
        }
    }
}
if (!$gateway) {
    echo "   ✗ No se pudo detectar el gateway\n";
}
echo "\n";

// Método 3: Probar conectividad a posibles IPs
echo "3. Probando conectividad SQL Server en diferentes hosts:\n";
$hostsToTest = array_filter([$hostIp, $gateway, '172.17.0.1', '192.168.65.254']);
$port = 1433;
$workingHost = null;

foreach ($hostsToTest as $testHost) {
    echo "   Probando $testHost:$port ... ";
    $connection = @fsockopen($testHost, $port, $errno, $errstr, 2);
    if ($connection) {
        echo "✓ CONECTA!\n";
        $workingHost = $testHost;
        fclose($connection);
        break;
    } else {
        echo "✗ ($errstr)\n";
    }
}

echo "\n";

if ($workingHost) {
    echo "=== RESULTADO ===\n";
    echo "✓ Usa este host en tu .env:\n";
    echo "DB_HOST=$workingHost\n";
    echo "\nO reinicia docker-compose con:\n";
    echo "docker-compose down\n";
    echo "DB_HOST=$workingHost docker-compose up -d\n";
} else {
    echo "=== NO SE ENCONTRÓ CONEXIÓN ===\n";
    echo "Verifica que:\n";
    echo "1. SQL Server esté corriendo en Windows\n";
    echo "2. TCP/IP esté habilitado en SQL Server Configuration Manager\n";
    echo "3. El puerto 1433 esté abierto en el firewall\n";
    echo "4. SQL Server esté escuchando en 0.0.0.0 o en todas las interfaces\n";
    echo "\nComandos útiles en Windows:\n";
    echo "- Get-Service MSSQL*\n";
    echo "- netstat -ano | findstr 1433\n";
    echo "- New-NetFirewallRule -DisplayName 'SQL Server' -Direction Inbound -Protocol TCP -LocalPort 1433 -Action Allow\n";
}

echo "\n";
