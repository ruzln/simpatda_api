<?php
$dsn = "firebird:dbname=localhost:C:/GIM/SERUYAN/SIMPATDA/DB/simpatda;charset=NONE";

try {
    $pdo = new PDO($dsn, "SYSDBA", "masterkey");
    echo "OK: PDO Firebird terkoneksi\n";
} catch (PDOException $e) {
    echo "GAGAL: " . $e->getMessage() . "\n";
}
