<?php
// Conectar ao banco de dados
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'barbearia';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Receber os dados do formulário
$cliente_id = 14; // ID do cliente, você pode substituir isso com dados de sessão ou outra lógica
$servico_id = $_POST['servico'];
$barbeiro_id = $_POST['barbeiro'];
$data_agendamento = $_POST['data_agendamento'];
$hora_agendamento = $_POST['hora_agendamento'];
$tenant_id = 1;

// Combinar data e hora para a data completa do agendamento
$data_hora_agendamento = $data_agendamento . ' ' . $hora_agendamento . ':00';

// Validar se o horário está disponível para o serviço
$query_verificar_disponibilidade = "
    SELECT COUNT(*) AS count
    FROM agendamentos
    WHERE tenant_id = ? AND servico_id = ? AND DATE(data_agendamento) = ? AND TIME(data_agendamento) = ? AND status = 'confirmado'
";

$stmt_verificar_disponibilidade = $conn->prepare($query_verificar_disponibilidade);
$stmt_verificar_disponibilidade->bind_param('iiss', $tenant_id, $servico_id, $data_agendamento, $hora_agendamento);
$stmt_verificar_disponibilidade->execute();
$result_verificar = $stmt_verificar_disponibilidade->get_result();
$row = $result_verificar->fetch_assoc();

if ($row['count'] > 0) {
    // O horário está ocupado
    echo "Este horário já está ocupado. Por favor, escolha outro.";
    exit;
}

// Verificar se o barbeiro está disponível
$query_verificar_indisponibilidade = "
    SELECT COUNT(*) AS count
    FROM barbeiro_indisponibilidade
    WHERE barbeiro_id = ? AND ((? BETWEEN data_inicio AND data_fim) OR (? BETWEEN data_inicio AND data_fim))
";

$stmt_verificar_indisponibilidade = $conn->prepare($query_verificar_indisponibilidade);
$stmt_verificar_indisponibilidade->bind_param('iss', $barbeiro_id, $data_hora_agendamento, $data_hora_agendamento);
$stmt_verificar_indisponibilidade->execute();
$result_indisponibilidade = $stmt_verificar_indisponibilidade->get_result();
$row_indisponibilidade = $result_indisponibilidade->fetch_assoc();

if ($row_indisponibilidade['count'] > 0) {
    // O barbeiro está indisponível nesse horário
    echo "O barbeiro não pode atender nesse horário. Por favor, escolha outro.";
    exit;
}

// Inserir o agendamento no banco de dados
$query_inserir_agendamento = "
    INSERT INTO agendamentos (tenant_id, cliente_id, servico_id, barbeiro_id, data_agendamento, status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, 'confirmado', NOW(), NOW())
";

$stmt_inserir_agendamento = $conn->prepare($query_inserir_agendamento);
$stmt_inserir_agendamento->bind_param('iiiss', $tenant_id, $cliente_id, $servico_id, $barbeiro_id, $data_hora_agendamento);

if ($stmt_inserir_agendamento->execute()) {
    // Agendamento inserido com sucesso
    echo "Seu agendamento foi realizado com sucesso! Aguarde a confirmação.";
} else {
    // Erro ao inserir o agendamento
    echo "Ocorreu um erro ao realizar o agendamento. Tente novamente.";
}

// Fechar a conexão
$conn->close();
?>