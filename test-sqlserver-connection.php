<?php
/**
 * Script de diagnóstico para conexión SQL Server desde Docker
 * Ejecutar: docker-compose exec app php test-sqlserver-connection.php
 */

echo "=== Diagnóstico de Conexión SQL Server ===\n\n";

// 1. Verificar extensiones PHP
echo "1. Extensiones PHP instaladas:\n";
$extensions = get_loaded_extensions();
echo "   - pdo_sqlsrv: " . (in_array('pdo_sqlsrv', $extensions) ? "✓ Instalada" : "✗ NO instalada") . "\n";
echo "   - sqlsrv: " . (in_array('sqlsrv', $extensions) ? "✓ Instalada" : "✗ NO instalada") . "\n\n";

// 2. Verificar variables de entorno
echo "2. Variables de entorno:\n";
echo "   DB_HOST: " . getenv('DB_HOST') . "\n";
echo "   DB_PORT: " . getenv('DB_PORT') . "\n";
echo "   DB_DATABASE: " . getenv('DB_DATABASE') . "\n";
echo "   DB_USERNAME: " . getenv('DB_USERNAME') . "\n";
echo "   DB_PASSWORD: " . (getenv('DB_PASSWORD') ? "***configurada***" : "NO configurada") . "\n\n";

// 3. Verificar resolución de host.docker.internal
echo "3. Resolución DNS:\n";
$host = getenv('DB_HOST') ?: 'host.docker.internal';
$ip = gethostbyname($host);
echo "   $host resuelve a: $ip\n";
if ($ip === $host) {
    echo "   ⚠ ADVERTENCIA: No se pudo resolver el host!\n";
}
echo "\n";

// 4. Verificar conectividad TCP
echo "4. Prueba de conectividad TCP:\n";
$host = getenv('DB_HOST') ?: 'host.docker.internal';
$port = getenv('DB_PORT') ?: '1433';
$timeout = 5;

$connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
if ($connection) {
    echo "   ✓ Puerto $port está abierto en $host\n";
    fclose($connection);
} else {
    echo "   ✗ No se puede conectar al puerto $port en $host\n";
    echo "   Error: $errstr ($errno)\n";
}
echo "\n";

// 5. Intentar conexión SQL Server
echo "5. Prueba de conexión SQL Server:\n";
if (!in_array('pdo_sqlsrv', $extensions)) {
    echo "   ✗ No se puede probar - extensión pdo_sqlsrv no instalada\n";
} else {
    try {
        $host = getenv('DB_HOST') ?: 'host.docker.internal';
        $port = getenv('DB_PORT') ?: '1433';
        $database = getenv('DB_DATABASE');
        $username = getenv('DB_USERNAME');
        $password = getenv('DB_PASSWORD');

        $dsn = "sqlsrv:Server=$host,$port;Database=$database";
        echo "   DSN: $dsn\n";
        echo "   Usuario: $username\n";

        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        echo "   ✓ Conexión exitosa!\n";

        // Probar una query simple
        $stmt = $pdo->query("SELECT @@VERSION as version");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   SQL Server Version: " . substr($result['version'], 0, 100) . "...\n";

    } catch (PDOException $e) {
        echo "   ✗ Error de conexión: " . $e->getMessage() . "\n";
        echo "   Código de error: " . $e->getCode() . "\n";
    }
}

echo "\n=== Fin del diagnóstico ===\n";
