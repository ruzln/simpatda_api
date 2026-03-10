<?php

$dsn = "firebird:dbname=localhost:C:/GIM/SERUYAN/SIMPATDA/DB/simpatda;charset=NONE";

$pdo = new PDO($dsn, "SYSDBA", "masterkey");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = "SELECT * FROM DAFTAR_SKPRD_V2(?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    '2024-01-01',
    '2024-12-31',
    'REKLA',
    0
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($data);
