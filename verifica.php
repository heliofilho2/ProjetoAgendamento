<?php
/// Conectar ao banco de dados
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'barbearia';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Erro de conexão com o banco de dados.']);
    exit;
}

$data_agendamento = $_GET['data'];
$servico_id = $_GET['servico_id'];
$tenant_id = 1; // Id da barbearia
$barbeiro_id = $_GET['barbeiro_id']; // Id do barbeiro (deve vir da solicitação)

$dias_semana = [
    'Sunday' => 'domingo',
    'Monday' => 'segunda',
    'Tuesday' => 'terça',
    'Wednesday' => 'quarta',
    'Thursday' => 'quinta',
    'Friday' => 'sexta',
    'Saturday' => 'sábado'
];

// Determinar o dia da semana
$dia_semana_ingles = date('l', strtotime($data_agendamento));
$dia_semana = $dias_semana[$dia_semana_ingles];

// Consulta os horários de funcionamento para o dia da semana
$query = "
    SELECT hora_abertura, hora_fechamento
    FROM horarios_funcionamento
    WHERE tenant_id = ? AND dia_semana = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param('is', $tenant_id, $dia_semana);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['error' => 'A barbearia não tem horário de funcionamento para este dia.']);
    exit;
}

$hora_abertura = $row['hora_abertura'];
$hora_fechamento = $row['hora_fechamento'];

// Consulta os agendamentos confirmados para o serviço no dia
$query_agendamentos = "
    SELECT DATE_FORMAT(data_agendamento, '%H:%i') AS hora_ocupada
    FROM agendamentos
    WHERE tenant_id = ? AND servico_id = ? AND DATE(data_agendamento) = ? AND status = 'confirmado'
";

$stmt_agendamentos = $conn->prepare($query_agendamentos);
$stmt_agendamentos->bind_param('iis', $tenant_id, $servico_id, $data_agendamento);
$stmt_agendamentos->execute();
$result_agendamentos = $stmt_agendamentos->get_result();

$agendamentos_ocupados = [];
while ($agendamento = $result_agendamentos->fetch_assoc()) {
    $agendamentos_ocupados[] = $agendamento['hora_ocupada'];
}

// Função para verificar se o barbeiro está disponível no horário solicitado
function verificarDisponibilidadeBarbeiro($barbeiro_id, $data_agendamento, $hora_agendamento, $duracao_servico, $conn)
{
    // Calcula o horário final do agendamento com base na duração do serviço
    $hora_fim_agendamento = date('Y-m-d H:i:s', strtotime("$hora_agendamento +$duracao_servico minutes"));

    // Verificar se o barbeiro está indisponível neste horário
    $query_indisponibilidade = "
        SELECT COUNT(*) AS count
        FROM barbeiro_indisponibilidade
        WHERE barbeiro_id = ? 
        AND (
            (data_inicio <= ? AND data_fim >= ?) OR
            (data_inicio <= ? AND data_fim >= ?) OR
            (? BETWEEN data_inicio AND data_fim)
        )
    ";

    $stmt_indisponibilidade = $conn->prepare($query_indisponibilidade);
    $stmt_indisponibilidade->bind_param('isssss', $barbeiro_id, $hora_agendamento, $hora_agendamento, $hora_fim_agendamento, $hora_fim_agendamento, $hora_agendamento);
    $stmt_indisponibilidade->execute();
    $result_indisponibilidade = $stmt_indisponibilidade->get_result();
    $row_indisponibilidade = $result_indisponibilidade->fetch_assoc();

    // Retorna true se o barbeiro estiver disponível (count == 0 significa que não há conflito com a indisponibilidade)
    return $row_indisponibilidade['count'] == 0;
}

// Função para calcular os horários disponíveis considerando a indisponibilidade do barbeiro
// Função para calcular os horários disponíveis considerando a indisponibilidade do barbeiro
function calcularHorariosDisponiveis($hora_abertura, $hora_fechamento, $duracao_servico, $agendamentos_ocupados, $barbeiro_id, $data_agendamento, $conn)
{
    $horarios_disponiveis = [];
    $hora_atual = strtotime($hora_abertura);

    while ($hora_atual < strtotime($hora_fechamento)) {
        $hora_formatada = date('H:i', $hora_atual);

        // Verifica se a hora está ocupada por algum agendamento confirmado
        if (!in_array($hora_formatada, $agendamentos_ocupados)) {
            // Verifica se o barbeiro está disponível neste horário, levando em consideração as indisponibilidades
            if (verificarDisponibilidadeBarbeiro($barbeiro_id, $data_agendamento, $hora_formatada, $duracao_servico, $conn)) {
                $horarios_disponiveis[] = $hora_formatada;
            }
        }

        // Avança para o próximo horário com a duração do serviço
        $hora_atual = strtotime("+$duracao_servico minutes", $hora_atual);
    }

    return $horarios_disponiveis;
}


// Duração do serviço
$query_servico = "SELECT duracao FROM servicos WHERE servico_id = ?";
$stmt_servico = $conn->prepare($query_servico);
$stmt_servico->bind_param('i', $servico_id);
$stmt_servico->execute();
$result_servico = $stmt_servico->get_result();
$servico = $result_servico->fetch_assoc();
$duracao_servico = $servico['duracao'];

// Calcular os horários disponíveis considerando a disponibilidade do barbeiro
$horarios_disponiveis = calcularHorariosDisponiveis($hora_abertura, $hora_fechamento, $duracao_servico, $agendamentos_ocupados, $barbeiro_id, $data_agendamento, $conn);

// Retornar os horários em formato JSON
echo json_encode(['horarios_disponiveis' => $horarios_disponiveis]);

// Fechar a conexão
$conn->close();

?>