<?php

$host = 'localhost';
$dbname = 'tccjoias26_v2';
$user = 'root';
$password = '';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    die(
        "Erro na conexão com o banco de dados: "
        . $e->getMessage()
    );
}