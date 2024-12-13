<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/barbearia/controllers/AuthController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/barbearia/config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$tenant_id = $_SESSION['tenant_id']; // Tenant ID da sessão

// Conexão com o banco de dados (ajuste conforme necessário)
$pdo = getDatabaseConnection();

// Função para gerar o relatório financeiro com filtros
function gerarRelatorioFinanceiro($data_inicio = '', $data_fim = '', $cliente_nome = '', $barbeiro_nome = '', $tenant_id)
{
    global $pdo;

    // Consultar os agendamentos confirmados com filtros e o tenant_id
    $query = "
        SELECT b.nome AS barbeiro_nome, a.barbeiro_id, a.agendamento_id AS agendamento_id, a.data_agendamento, a.cliente_id, 
               s.nome AS servico_nome, s.preco AS servico_valor, c.nome AS cliente_nome
        FROM agendamentos a
        JOIN servicos s ON a.servico_id = s.servico_id
        JOIN usuarios c ON a.cliente_id = c.user_id
        JOIN usuarios b ON a.barbeiro_id = b.user_id AND b.tipo = 'barbeiro'
        WHERE a.pago = 1 AND a.tenant_id = :tenant_id
    ";

    // Adicionar filtros à consulta
    if ($barbeiro_nome) {
        $query .= " AND b.nome LIKE :barbeiro_nome";
    }
    if ($data_inicio) {
        $query .= " AND DATE(a.data_agendamento) >= :data_inicio";
    }
    if ($data_fim) {
        $query .= " AND DATE(a.data_agendamento) <= :data_fim";
    }
    if ($cliente_nome) {
        $query .= " AND c.nome LIKE :cliente_nome";
    }

    $query .= " ORDER BY a.data_agendamento DESC";

    try {
        $stmt = $pdo->prepare($query);

        // Vincular parâmetros de filtros se fornecidos
        $stmt->bindParam(':tenant_id', $tenant_id);

        if ($barbeiro_nome) {
            $barbeiro_nome = "%{$barbeiro_nome}%"; // Adiciona o "%" para buscar por partes do nome
            $stmt->bindParam(':barbeiro_nome', $barbeiro_nome);
        }
        if ($data_inicio) {
            $stmt->bindParam(':data_inicio', $data_inicio);
        }
        if ($data_fim) {
            $stmt->bindParam(':data_fim', $data_fim);
        }
        if ($cliente_nome) {
            $cliente_nome = "%{$cliente_nome}%";
            $stmt->bindParam(':cliente_nome', $cliente_nome);
        }

        // Executar a consulta
        $stmt->execute();

        // Inicializar variáveis para o total
        $total_receita = 0;

        // Iniciar o HTML para o relatório
        echo '<h5>Relatório financeiro de agendamentos confirmados</h5>';
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Data do Agendamento</th>';
        echo '<th>Cliente</th>';
        echo '<th>Serviço</th>';
        echo '<th>Barbeiro</th>';
        echo '<th>Valor</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Verificar se há resultados
        if ($stmt->rowCount() > 0) {
            // Percorrer os resultados e exibir as informações
            while ($row = $stmt->fetch()) {
                // Formatar a data para o formato d/m/Y
                $data_agendamento = date('d/m/Y', strtotime($row['data_agendamento']));
                $cliente_nome = htmlspecialchars($row['cliente_nome']);
                $servico_nome = htmlspecialchars($row['servico_nome']);
                $barbeiro_nome = htmlspecialchars($row['barbeiro_nome']);
                $servico_valor = number_format($row['servico_valor'], 2, ',', '.'); // Formatar valor como R$ 100,00

                // Adicionar ao total
                $total_receita += $row['servico_valor'];

                // Exibir os dados do agendamento
                echo "<tr>";
                echo "<td>{$data_agendamento}</td>";
                echo "<td>{$cliente_nome}</td>";
                echo "<td>{$servico_nome}</td>";
                echo "<td>{$barbeiro_nome}</td>";
                echo "<td>R$ {$servico_valor}</td>";
                echo "</tr>";
            }
        } else {
            echo '<tr><td colspan="5">Nenhum agendamento confirmado encontrado.</td></tr>';
        }

        // Fechar a tabela
        echo '</tbody>';
        echo '</table>';

        // Exibir o total da receita
        $total_receita_formatado = number_format($total_receita, 2, ',', '.');
        echo "<h5>Total da Receita: R$ {$total_receita_formatado}</h5>";

    } catch (PDOException $e) {
        // Caso ocorra um erro na consulta
        echo "Erro ao consultar os dados: " . $e->getMessage();
    }
}

// Obter os filtros da URL (GET)
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$cliente_nome = isset($_GET['cliente_nome']) ? $_GET['cliente_nome'] : '';
$barbeiro_nome = isset($_GET['barbeiro_nome']) ? $_GET['barbeiro_nome'] : '';

// Chamar a função para gerar o relatório com os filtros
?>

<?php include('../partials/header.php') ?>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include('../partials/menu.php') ?>

            <!-- Main content -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Relatório Financeiro</h4>
                </div>

                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-header">
                        Filtros
                    </div>
                    <div class="card-body">
                        <form method="GET" action="finance.php">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <input type="text" name="cliente_nome" placeholder="Filtrar por cliente"
                                        class="form-control" value="<?php echo $cliente_nome; ?>" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="date" name="data_inicio" placeholder="Data Início" class="form-control"
                                        value="<?php echo $data_inicio; ?>" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="date" name="data_fim" placeholder="Data Fim" class="form-control"
                                        value="<?php echo $data_fim; ?>" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="text" name="barbeiro_nome" placeholder="Filtrar por barbeiro"
                                        class="form-control" value="<?php echo $barbeiro_nome; ?>" />
                                </div>
                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Relatório -->
                <?php gerarRelatorioFinanceiro($data_inicio, $data_fim, $cliente_nome, $barbeiro_nome, $tenant_id); ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>