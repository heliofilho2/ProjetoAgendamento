<?php
session_start();
require_once '../../controllers/AuthController.php'; // Inclui o controlador de autenticação
require_once '../../models/Agendamentos.php'; // Inclui o modelo Agendamentos

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Conexão com o banco de dados (ajuste conforme necessário)
$pdo = getDatabaseConnection();
$agendamentosModel = new Agendamento($pdo);

// Variáveis de erro e sucesso
$error = '';
$success = '';

// Ação de logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $authController = new AuthController();
    $authController->logout();
}

// Filtragem de agendamentos
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$cliente_nome = isset($_GET['cliente_nome']) ? $_GET['cliente_nome'] : ''; // Adicionado o filtro de nome de cliente
$tenant_id = $_SESSION['tenant_id']; // Assumindo que o tenant_id seja fixo ou recuperado da sessão

// Obtenção dos agendamentos com os filtros aplicados
$agendamentos = $agendamentosModel->getAgendamentosByTenant($tenant_id, $cliente_nome, $data_inicio, $data_fim);

// Ação de edição de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_agendamento'])) {
    try {
        // Coleta os dados do formulário
        $agendamento_id = $_POST['agendamento_id'];
        $data_agendamento = $_POST['data_agendamento'];
        $hora_agendamento = $_POST['hora_agendamento'];
        $cliente_id = $_POST['cliente_id'];
        $servico_id = $_POST['servico_id'];
        $status = $_POST['status'];
        $observacoes = $_POST['observacoes'];

        // Atualiza o agendamento
        $agendamentosModel->updateAgendamento($agendamento_id, $data_agendamento, $hora_agendamento, $cliente_id, $servico_id, $status, $observacoes);
        $success = 'Agendamento atualizado com sucesso.';

        // Redireciona para a mesma página com os dados atualizados
        header('Location: agendamentos.php?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage(); // Captura o erro, caso ocorra
    }
}

// Ação de exclusão de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_agendamento'])) {
    try {
        // Coleta o ID do agendamento
        $agendamento_id = $_POST['agendamento_id'];

        // Exclui o agendamento
        $agendamentosModel->deleteAgendamento($agendamento_id);
        $success = 'Agendamento excluído com sucesso.';

        // Redireciona para a mesma página com os dados atualizados
        header('Location: agendamentos.php?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage(); // Captura o erro, caso ocorra
    }
}
?>

<?php include('../partials/header.php') ?>

<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include('../partials/menu.php') ?>

            <!-- Main content -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4>Gestão de Agendamentos</h4>
                </div>

                <!-- Mensagens de sucesso ou erro -->
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo $_GET['success']; ?></div>
                <?php endif; ?>

                <!-- Card de Filtro -->
                <div class="card mb-4">
                    <div class="card-header">
                        Filtros
                    </div>
                    <div class="card-body">
                        <form method="GET" action="agendamentos.php">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <input type="text" name="cliente_nome" placeholder="Filtrar por cliente"
                                        class="form-control" value="<?php echo $cliente_nome; ?>" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="date" name="data_inicio" placeholder="Filtrar por data inicial"
                                        class="form-control" value="<?php echo $data_inicio; ?>" />
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input type="date" name="data_fim" placeholder="Filtrar por data final"
                                        class="form-control" value="<?php echo $data_fim; ?>" />
                                </div>
                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Card de Tabela de Agendamentos -->
                <div class="card">
                    <div class="card-header">
                        Agendamentos
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Barbeiro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr>
                                        <td><?php echo $agendamento['agendamento_id']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                        <td><?php echo $agendamento['hora_agendamento']; ?></td>
                                        <td><?php echo $agendamento['cliente_nome']; ?></td>
                                        <td><?php echo $agendamento['servico_nome']; ?></td>
                                        <td><?php echo $agendamento['barbeiro_nome']; ?></td>
                                        <td>
                                            <!--<button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editAgendamentoModal"
                                                data-agendamento_id="<?php echo $agendamento['agendamento_id']; ?>"
                                                data-data_agendamento="<?php echo $agendamento['data_agendamento']; ?>"
                                                data-hora_agendamento="<?php echo $agendamento['hora_agendamento']; ?>"
                                                data-cliente_id="<?php echo $agendamento['cliente_id']; ?>"
                                                data-servico_id="<?php echo $agendamento['servico_id']; ?>"
                                                data-status="<?php echo $agendamento['status']; ?>"
                                                data-observacoes="<?php echo $agendamento['observacoes']; ?>">Editar</button>-->
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteAgendamentoModal"
                                                data-agendamento_id="<?php echo $agendamento['agendamento_id']; ?>">Excluir</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Editar Agendamento -->
    <div class="modal fade" id="editAgendamentoModal" tabindex="-1" aria-labelledby="editAgendamentoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAgendamentoModalLabel">Editar Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="agendamentos.php" method="POST">
                        <input type="hidden" name="agendamento_id" id="editAgendamentoId">
                        <div class="mb-3">
                            <label for="editDataAgendamento" class="form-label">Data</label>
                            <input type="date" class="form-control" name="data_agendamento" id="editDataAgendamento"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="editHoraAgendamento" class="form-label">Hora</label>
                            <input type="time" class="form-control" name="hora_agendamento" id="editHoraAgendamento"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="editClienteNome" class="form-label">Cliente</label>
                            <input type="text" class="form-control" name="cliente_nome" id="editClienteNome" required
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editServicoNome" class="form-label">Serviço</label>
                            <input type="text" class="form-control" name="servico_nome" id="editServicoNome" required
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <input type="text" class="form-control" name="status" id="editStatus" required>
                        </div>
                        <div class="mb-3">
                            <label for="editObservacoes" class="form-label">Observações</label>
                            <input type="text" class="form-control" name="observacoes" id="editObservacoes" required>
                        </div>
                        <button type="submit" name="update_agendamento" class="btn btn-warning">Atualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Agendamento -->
    <div class="modal fade" id="deleteAgendamentoModal" tabindex="-1" aria-labelledby="deleteAgendamentoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAgendamentoModalLabel">Excluir Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Você tem certeza de que deseja excluir este agendamento?</p>
                    <form action="agendamentos.php" method="POST">
                        <input type="hidden" name="agendamento_id" id="deleteAgendamentoId">
                        <button type="submit" name="delete_agendamento" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Preencher os campos do modal de edição
        var editModal = document.getElementById('editAgendamentoModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var agendamento_id = button.getAttribute('data-agendamento_id');
            var data_agendamento = button.getAttribute('data-data_agendamento');
            var hora_agendamento = button.getAttribute('data-hora_agendamento');
            var cliente_id = button.getAttribute('data-cliente_id');
            var servico_id = button.getAttribute('data-servico_id');
            var status = button.getAttribute('data-status');
            var observacoes = button.getAttribute('data-observacoes');

            // Buscar o nome do cliente e serviço através dos IDs
            fetch(`get_client_service.php?cliente_id=${cliente_id}&servico_id=${servico_id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editClienteNome').value = data.cliente_nome;
                    document.getElementById('editServicoNome').value = data.servico_nome;
                });

            document.getElementById('editAgendamentoId').value = agendamento_id;
            document.getElementById('editDataAgendamento').value = data_agendamento;
            document.getElementById('editHoraAgendamento').value = hora_agendamento;
            document.getElementById('editStatus').value = status;
            document.getElementById('editObservacoes').value = observacoes;
        });
    </script>
</body>

</html>