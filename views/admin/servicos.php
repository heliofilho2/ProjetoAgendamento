<?php
session_start();
require_once '../../controllers/AuthController.php'; // Inclui o controlador de autenticação
require_once '../../models/Services.php'; // Inclui o modelo Service

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Conexão com o banco de dados (ajuste conforme necessário)
$pdo = getDatabaseConnection();
$serviceModel = new Service($pdo);

// Variáveis de erro e sucesso
$error = '';
$success = '';

// Ação de logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    $authController = new AuthController();
    $authController->logout();
}

// Filtragem de serviços
$nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$descricao = isset($_GET['descricao']) ? $_GET['descricao'] : '';
$tenant_id = 1; // Assumindo que o tenant_id seja fixo ou recuperado da sessão

// Obtenção dos serviços
$services = $serviceModel->getServicesByTenant($tenant_id, $nome, $descricao);

// Ação de cadastro de serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_service'])) {
    try {
        // Coleta os dados do formulário
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $duracao = $_POST['duracao'];
        $preco = $_POST['preco'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        // Criação do novo serviço
        $serviceModel->createService($tenant_id, $nome, $descricao, $duracao, $preco, $ativo);
        $success = 'Serviço cadastrado com sucesso.';

        // Redireciona para a mesma página com os dados atualizados
        header('Location: servicos.php?success=' . urlencode($success));
    } catch (Exception $e) {
        $error = $e->getMessage(); // Captura o erro, caso ocorra
    }
}

// Ação de edição de serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    try {
        // Coleta os dados do formulário
        $servico_id = $_POST['servico_id'];  // Ajustado para servico_id
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $duracao = $_POST['duracao'];
        $preco = $_POST['preco'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        // Atualiza o serviço
        $serviceModel->updateService($servico_id, $nome, $descricao, $duracao, $preco, $ativo);
        $success = 'Serviço atualizado com sucesso.';

        // Redireciona para a mesma página com os dados atualizados
        header('Location: servicos.php?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage(); // Captura o erro, caso ocorra
    }
}

// Ação de exclusão de serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service'])) {
    try {
        // Coleta o ID do serviço
        $servico_id = $_POST['servico_id'];  // Ajustado para servico_id

        // Exclui o serviço
        $serviceModel->deleteService($servico_id);
        $success = 'Serviço excluído com sucesso.';

        // Redireciona para a mesma página com os dados atualizados
        header('Location: servicos.php?success=' . urlencode($success));
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
                    <h4>Gestão de Serviços</h4>

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
                        <form method="GET" action="servicos.php">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="text" name="nome" placeholder="Filtrar por nome" class="form-control"
                                        value="<?php echo $nome; ?>" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="text" name="descricao" placeholder="Filtrar por descrição"
                                        class="form-control" value="<?php echo $descricao; ?>" />
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">Cadastrar
                    Serviço</button>
                <!-- Card de Tabela de Serviços -->
                <div class="card">
                    <div class="card-header">
                        Serviços
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Duração</th>
                                    <th>Preço</th>
                                    <th>Ativo</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($services as $service): ?>
                                    <tr>
                                        <td><?php echo $service['servico_id']; ?></td> <!-- Ajustado para servico_id -->
                                        <td><?php echo $service['nome']; ?></td>
                                        <td><?php echo $service['descricao']; ?></td>
                                        <td><?php echo $service['duracao']; ?> minutos</td>
                                        <td><?php echo 'R$ ' . number_format($service['preco'], 2, ',', '.'); ?></td>
                                        <td><?php echo $service['ativo'] ? 'Sim' : 'Não'; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editServiceModal"
                                                data-servico_id="<?php echo $service['servico_id']; ?>"
                                                data-nome="<?php echo $service['nome']; ?>"
                                                data-descricao="<?php echo $service['descricao']; ?>"
                                                data-duracao="<?php echo $service['duracao']; ?>"
                                                data-preco="<?php echo $service['preco']; ?>"
                                                data-ativo="<?php echo $service['ativo']; ?>">Editar</button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#deleteServiceModal"
                                                data-servico_id="<?php echo $service['servico_id']; ?>">Excluir</button>
                                            <!-- Ajustado -->
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

    <!-- Modal Cadastrar Serviço -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">Cadastrar Novo Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="servicos.php" method="POST">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="duracao" class="form-label">Duração (minutos)</label>
                            <input type="number" class="form-control" name="duracao" required>
                        </div>
                        <div class="mb-3">
                            <label for="preco" class="form-label">Preço</label>
                            <input type="number" step="0.01" class="form-control" name="preco" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="ativo" checked>
                            <label class="form-check-label" for="ativo">Ativo</label>
                        </div>
                        <button type="submit" name="create_service" class="btn btn-primary">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Serviço -->
    <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editServiceModalLabel">Editar Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="servicos.php" method="POST">
                        <input type="hidden" name="servico_id" id="editServiceId"> <!-- Ajustado -->
                        <div class="mb-3">
                            <label for="editNome" class="form-label">Nome</label>
                            <input type="text" class="form-control" name="nome" id="editNome" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescricao" class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" id="editDescricao" rows="3"
                                required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editDuracao" class="form-label">Duração (minutos)</label>
                            <input type="number" class="form-control" name="duracao" id="editDuracao" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPreco" class="form-label">Preço</label>
                            <input type="number" step="0.01" class="form-control" name="preco" id="editPreco" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="ativo" id="editAtivo">
                            <label class="form-check-label" for="editAtivo">Ativo</label>
                        </div>
                        <button type="submit" name="update_service" class="btn btn-warning">Atualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Excluir Serviço -->
    <div class="modal fade" id="deleteServiceModal" tabindex="-1" aria-labelledby="deleteServiceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteServiceModalLabel">Excluir Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Você tem certeza de que deseja excluir este serviço?</p>
                    <form action="servicos.php" method="POST">
                        <input type="hidden" name="servico_id" id="deleteServiceId"> <!-- Ajustado -->
                        <button type="submit" name="delete_service" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Preencher os campos do modal de edição
        var editModal = document.getElementById('editServiceModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Botão que abriu o modal
            var servico_id = button.getAttribute('data-servico_id'); // Ajustado
            var nome = button.getAttribute('data-nome');
            var descricao = button.getAttribute('data-descricao');
            var duracao = button.getAttribute('data-duracao');
            var preco = button.getAttribute('data-preco');
            var ativo = button.getAttribute('data-ativo') === '1';

            // Preencher os campos
            document.getElementById('editServiceId').value = servico_id;
            document.getElementById('editNome').value = nome;
            document.getElementById('editDescricao').value = descricao;
            document.getElementById('editDuracao').value = duracao;
            document.getElementById('editPreco').value = preco;
            document.getElementById('editAtivo').checked = ativo;
        });

        // Preencher o ID do serviço para excluir
        var deleteModal = document.getElementById('deleteServiceModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var servico_id = button.getAttribute('data-servico_id'); // Ajustado
            document.getElementById('deleteServiceId').value = servico_id;
        });
    </script>
</body>

</html>