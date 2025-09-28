<?php
// config.php

// Configurações do Banco de Dados
define('DB_HOST', 'localhost'); // Geralmente é 'localhost'
define('DB_NAME', 'todolist_db'); // Nome do banco de dados criado
define('DB_USER', 'root'); // Seu usuário do MySQL
define('DB_PASS', ''); // Sua senha do MySQL

try {
    // Cria a instância de PDO (conexão)
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Em caso de falha na conexão
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
?>