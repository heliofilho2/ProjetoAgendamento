<?php
// config/config.php

// Definir as credenciais de conexão com o banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'barbearia');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Função para obter a conexão PDO
function getDatabaseConnection()
{
    // Verifica se a conexão PDO já existe
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            // Cria a conexão PDO
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // Caso haja erro na conexão, exibe a mensagem de erro
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    // Retorna a conexão PDO
    return $pdo;
}
?>