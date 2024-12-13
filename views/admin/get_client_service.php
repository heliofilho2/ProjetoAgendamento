<?php
// Conexão com o banco de dados
require_once '../../config/config.php';
$pdo = getDatabaseConnection();

// Recebe os IDs de cliente e serviço via GET
$cliente_id = $_GET['cliente_id'];
$servico_id = $_GET['servico_id'];

// Busca os nomes no banco de dados
$cliente_stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE user_id = ?");
$cliente_stmt->execute([$cliente_id]);
$cliente = $cliente_stmt->fetch(PDO::FETCH_ASSOC);

$servico_stmt = $pdo->prepare("SELECT nome FROM servicos WHERE servico_id = ?");
$servico_stmt->execute([$servico_id]);
$servico = $servico_stmt->fetch(PDO::FETCH_ASSOC);

// Retorna os dados em formato JSON
echo json_encode([
    'cliente_nome' => $cliente['nome'],
    'servico_nome' => $servico['nome']
]);
?>